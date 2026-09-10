<?php

namespace Tests\Feature;

use App\Enums\ChallengeStatus;
use App\Enums\PaymentStatus;
use App\Models\Challenge;
use App\Models\Inscription;
use App\Models\Participante;
use App\Models\User;
use Database\Seeders\FitnessReferenceSeeder;
use Database\Seeders\ImproveRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class InscriptionManagementTest extends TestCase
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
    public function manager_peut_inscrire_une_participante_existante_a_une_session_existante(): void
    {
        $manager = $this->manager();
        $participante = Participante::factory()->create();
        $challenge = Challenge::factory()->create();

        $response = $this->actingAs($manager)->post(route('inscriptions.store'), $this->existingPayload($participante, $challenge, [
            'goal_text' => 'Perte de poids progressive',
            'goal_weight' => 70,
            'price' => 30000,
        ]));

        $inscription = Inscription::query()->firstOrFail();

        $response->assertRedirect(route('inscriptions.show', $inscription));
        $this->assertSame($participante->id, $inscription->participante_id);
        $this->assertSame($challenge->id, $inscription->challenge_id);
        $this->assertSame(ChallengeStatus::Planifie, $inscription->status);
        $this->assertSame(PaymentStatus::Impaye, $inscription->payment_status);
        $this->assertSame('Perte de poids progressive', $inscription->goal_text);
        $this->assertSame($manager->id, $inscription->created_by);
    }

    #[Test]
    public function manager_peut_creer_une_nouvelle_participante_et_son_inscription_en_une_requete(): void
    {
        $manager = $this->manager();
        $challenge = Challenge::factory()->create();

        $response = $this->actingAs($manager)->post(route('inscriptions.store'), [
            'participante_mode' => 'new',
            'participante' => [
                'first_name' => 'Aminata',
                'last_name' => 'Diallo',
                'phone' => '620000010',
                'email' => 'aminata.inscription@example.com',
                'address' => 'Ouagadougou',
                'birthdate' => '1995-03-20',
                'status' => 'active',
                'has_cesarean' => '1',
                'cesarean_comment' => 'Déclaration initiale',
                'health_notes' => 'Suivi doux',
                'registration_date' => '2026-08-22',
            ],
            'challenge_mode' => 'existing',
            'challenge_id' => $challenge->id,
            'inscription' => [
                'status' => ChallengeStatus::Planifie->value,
                'price' => 42500,
                'goal_text' => 'Perte de poids progressive',
                'goal_weight' => 70,
                'goal_waist' => 85,
            ],
        ]);

        $participante = Participante::query()->where('phone', '620000010')->firstOrFail();
        $inscription = Inscription::query()->firstOrFail();

        $response->assertRedirect(route('inscriptions.show', $inscription));
        $this->assertDatabaseCount('participantes', 1);
        $this->assertDatabaseCount('challenges', 1);
        $this->assertSame($participante->id, $inscription->participante_id);
        $this->assertSame($challenge->id, $inscription->challenge_id);
        $this->assertTrue($participante->has_cesarean);
        $this->assertSame($manager->id, $participante->created_by);
        $this->assertSame($manager->id, $inscription->created_by);
    }

    #[Test]
    public function edition_modifie_les_objectifs_le_prix_et_le_statut_de_progression(): void
    {
        $manager = $this->manager();
        $inscription = Inscription::factory()->create([
            'status' => ChallengeStatus::Planifie,
            'price' => 30000,
            'goal_text' => 'Objectif initial',
        ]);

        $response = $this->actingAs($manager)->put(route('inscriptions.update', $inscription), [
            'participante_id' => $inscription->participante_id,
            'challenge_id' => $inscription->challenge_id,
            'status' => ChallengeStatus::EnCours->value,
            'price' => 35000,
            'goal_text' => 'Nouvel objectif',
            'goal_weight' => 68,
            'goal_waist' => 80,
            'goal_personal' => 'Marcher trois fois par semaine',
            'observations' => 'Suivi démarré',
        ]);

        $response->assertRedirect(route('inscriptions.show', $inscription));
        $inscription->refresh();
        $this->assertSame(ChallengeStatus::EnCours, $inscription->status);
        $this->assertSame('35000.00', $inscription->price);
        $this->assertSame('Nouvel objectif', $inscription->goal_text);
        $this->assertSame($manager->id, $inscription->updated_by);
    }

    #[Test]
    public function manager_peut_supprimer_une_inscription_sans_donnee_historique(): void
    {
        $manager = $this->manager();
        $inscription = Inscription::factory()->create();

        $this->actingAs($manager)
            ->delete(route('inscriptions.destroy', $inscription))
            ->assertRedirect(route('challenges.show', $inscription->challenge));

        $this->assertSoftDeleted('inscriptions', ['id' => $inscription->id]);
    }

    #[Test]
    public function doublon_participante_session_est_rejete_par_la_validation(): void
    {
        $manager = $this->manager();
        $inscription = Inscription::factory()->create();

        $response = $this->actingAs($manager)
            ->from(route('inscriptions.create'))
            ->post(route('inscriptions.store'), $this->existingPayload($inscription->participante, $inscription->challenge));

        $response->assertRedirect(route('inscriptions.create'));
        $response->assertSessionHasErrors('challenge_id');
        $this->assertSame(1, Inscription::query()->count());
    }

    #[Test]
    public function utilisateur_sans_permission_ne_peut_pas_gerer_les_inscriptions(): void
    {
        $user = User::factory()->create();
        $inscription = Inscription::factory()->create();

        $this->actingAs($user)->get(route('inscriptions.index'))->assertForbidden();
        $this->actingAs($user)->get(route('inscriptions.create'))->assertForbidden();
        $this->actingAs($user)->post(route('inscriptions.store'), [])->assertForbidden();
        $this->actingAs($user)->put(route('inscriptions.update', $inscription), [])->assertForbidden();
        $this->actingAs($user)->delete(route('inscriptions.destroy', $inscription))->assertForbidden();
    }

    private function manager(): User
    {
        $manager = User::factory()->create();
        $manager->assignRole('manager');

        return $manager;
    }

    private function existingPayload(Participante $participante, Challenge $challenge, array $inscription = []): array
    {
        return [
            'participante_mode' => 'existing',
            'participante_id' => $participante->id,
            'challenge_mode' => 'existing',
            'challenge_id' => $challenge->id,
            'inscription' => $inscription + [
                'status' => ChallengeStatus::Planifie->value,
                'price' => 30000,
            ],
        ];
    }
}
