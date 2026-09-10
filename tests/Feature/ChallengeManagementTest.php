<?php

namespace Tests\Feature;

use App\Enums\PaymentMode;
use App\Enums\PaymentType;
use App\Models\Challenge;
use App\Models\ChallengeType;
use App\Models\Inscription;
use App\Models\Paiement;
use App\Models\User;
use Database\Seeders\FitnessReferenceSeeder;
use Database\Seeders\ImproveRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ChallengeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ImproveRolesAndPermissionsSeeder::class);
        $this->seed(FitnessReferenceSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    #[Test]
    public function manager_peut_creer_une_session_sans_participante_avec_date_de_fin_calculee(): void
    {
        $manager = $this->manager();
        $challengeType = ChallengeType::query()->where('code', 'perte_de_poids')->firstOrFail();

        $response = $this->actingAs($manager)->post(route('challenges.store'), [
            'challenge_type_id' => $challengeType->id,
            'start_date' => '2026-08-10',
            'duration_days' => 15,
        ]);

        $challenge = Challenge::query()->firstOrFail();

        $response->assertRedirect(route('challenges.show', $challenge));
        $this->assertSame('2026-08-25', $challenge->end_date->toDateString());
        $this->assertSame($manager->id, $challenge->created_by);
        $this->assertSame(0, $challenge->inscriptions()->count());
    }

    #[Test]
    public function duree_de_la_session_est_validee_depuis_la_config(): void
    {
        $manager = $this->manager();
        $challengeType = ChallengeType::query()->where('code', 'perte_de_poids')->firstOrFail();

        $response = $this->actingAs($manager)->post(route('challenges.store'), [
            'challenge_type_id' => $challengeType->id,
            'start_date' => '2026-08-10',
            'duration_days' => 14,
        ]);

        $response->assertSessionHasErrors('duration_days');
        $this->assertDatabaseCount('challenges', 0);
    }

    #[Test]
    public function coach_ne_peut_pas_creer_de_session(): void
    {
        $coach = User::factory()->create();
        $coach->assignRole('coach');

        $this->actingAs($coach)->get(route('challenges.create'))->assertForbidden();
        $this->actingAs($coach)->post(route('challenges.store'), [])->assertForbidden();
    }

    #[Test]
    public function manager_peut_modifier_le_planning_d_une_session_vide(): void
    {
        $manager = $this->manager();
        $challenge = $this->challenge([
            'start_date' => '2026-08-10',
            'duration_days' => 15,
        ]);

        $this->actingAs($manager)
            ->put(route('challenges.update', $challenge), $this->sessionPayload($challenge, [
                'start_date' => '2026-08-12',
                'duration_days' => 30,
            ]))
            ->assertRedirect(route('challenges.show', $challenge));

        $challenge->refresh();
        $this->assertSame('2026-08-12', $challenge->start_date->toDateString());
        $this->assertSame('2026-09-11', $challenge->end_date->toDateString());
        $this->assertSame($manager->id, $challenge->updated_by);
    }

    #[Test]
    public function changement_de_planning_avec_historique_demande_confirmation(): void
    {
        $manager = $this->manager();
        $challenge = $this->challenge([
            'start_date' => '2026-08-10',
            'duration_days' => 15,
        ]);
        $inscription = Inscription::factory()->create([
            'challenge_id' => $challenge->id,
            'created_by' => $manager->id,
        ]);
        Paiement::query()->create([
            'inscription_id' => $inscription->id,
            'amount' => 10000,
            'type' => PaymentType::Paiement,
            'payment_date' => '2026-08-10',
            'payment_mode' => PaymentMode::Especes,
            'recorded_by' => $manager->id,
        ]);

        $payload = $this->sessionPayload($challenge, ['duration_days' => 30]);

        $this->actingAs($manager)
            ->put(route('challenges.update', $challenge), $payload)
            ->assertRedirect()
            ->assertSessionHas('warning');

        $this->assertSame('2026-08-25', $challenge->fresh()->end_date->toDateString());

        $this->actingAs($manager)
            ->put(route('challenges.update', $challenge), $payload + ['confirm_schedule_change' => '1'])
            ->assertRedirect(route('challenges.show', $challenge));

        $this->assertSame('2026-09-09', $challenge->fresh()->end_date->toDateString());
    }

    #[Test]
    public function manager_peut_supprimer_une_session_sans_inscription(): void
    {
        $manager = $this->manager();
        $challenge = $this->challenge();

        $this->actingAs($manager)
            ->delete(route('challenges.destroy', $challenge))
            ->assertRedirect(route('challenges.index'));

        $this->assertSoftDeleted('challenges', ['id' => $challenge->id]);
    }

    #[Test]
    public function une_session_avec_inscription_meme_vide_ne_peut_pas_etre_supprimee(): void
    {
        $manager = $this->manager();
        $challenge = $this->challenge();
        Inscription::factory()->create(['challenge_id' => $challenge->id]);

        $this->actingAs($manager)
            ->delete(route('challenges.destroy', $challenge))
            ->assertRedirect(route('challenges.show', $challenge))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('challenges', ['id' => $challenge->id, 'deleted_at' => null]);
    }

    private function manager(): User
    {
        $manager = User::factory()->create();
        $manager->assignRole('manager');

        return $manager;
    }

    private function challenge(array $attributes = []): Challenge
    {
        return Challenge::factory()->create($attributes);
    }

    private function sessionPayload(Challenge $challenge, array $overrides = []): array
    {
        return $overrides + [
            'challenge_type_id' => $challenge->challenge_type_id,
            'start_date' => $challenge->start_date->toDateString(),
            'duration_days' => $challenge->duration_days,
        ];
    }
}
