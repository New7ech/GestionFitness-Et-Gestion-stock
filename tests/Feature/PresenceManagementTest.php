<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Models\Challenge;
use App\Models\Inscription;
use App\Models\Participante;
use App\Models\Presence;
use App\Models\User;
use Database\Seeders\FitnessReferenceSeeder;
use Database\Seeders\ImproveRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PresenceManagementTest extends TestCase
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
    public function manager_peut_enregistrer_une_presence_sur_une_inscription(): void
    {
        $manager = $this->manager();
        $inscription = $this->inscription();

        $response = $this->actingAs($manager)->post(route('presences.store'), [
            'inscription_id' => $inscription->id,
            'attendance_date' => '2026-08-11',
            'status' => AttendanceStatus::Presente->value,
            'comment' => 'Séance complète.',
        ]);

        $presence = Presence::query()->firstOrFail();

        $response->assertRedirect(route('presences.show', $presence));
        $this->assertSame($inscription->id, $presence->inscription_id);
        $this->assertSame(AttendanceStatus::Presente, $presence->status);
        $this->assertSame($manager->id, $presence->recorded_by);
        $this->assertSame($manager->id, $presence->updated_by);
    }

    #[Test]
    public function creation_presence_valide_les_champs_obligatoires(): void
    {
        $manager = $this->manager();

        $response = $this->actingAs($manager)
            ->from(route('presences.create'))
            ->post(route('presences.store'), [
                'inscription_id' => '',
                'attendance_date' => '',
                'status' => '',
            ]);

        $response->assertRedirect(route('presences.create'));
        $response->assertSessionHasErrors(['inscription_id', 'attendance_date', 'status']);
        $this->assertDatabaseCount('presences', 0);
    }

    #[Test]
    public function une_seule_presence_est_autorisee_par_inscription_et_par_date(): void
    {
        $manager = $this->manager();
        $inscription = $this->inscription();

        Presence::factory()->create([
            'inscription_id' => $inscription->id,
            'attendance_date' => '2026-08-11',
            'status' => AttendanceStatus::Presente,
        ]);

        $response = $this->actingAs($manager)
            ->from(route('presences.create', ['inscription_id' => $inscription->id]))
            ->post(route('presences.store'), [
                'inscription_id' => $inscription->id,
                'attendance_date' => '2026-08-11',
                'status' => AttendanceStatus::Absente->value,
            ]);

        $response->assertRedirect(route('presences.create', ['inscription_id' => $inscription->id]));
        $response->assertSessionHasErrors('attendance_date');
        $this->assertSame(1, Presence::query()->where('inscription_id', $inscription->id)->count());
    }

    #[Test]
    public function date_presence_doit_rester_dans_la_periode_de_la_session(): void
    {
        $manager = $this->manager();
        $inscription = $this->inscription();

        $response = $this->actingAs($manager)
            ->from(route('presences.create', ['inscription_id' => $inscription->id]))
            ->post(route('presences.store'), [
                'inscription_id' => $inscription->id,
                'attendance_date' => '2026-09-15',
                'status' => AttendanceStatus::Presente->value,
            ]);

        $response->assertRedirect(route('presences.create', ['inscription_id' => $inscription->id]));
        $response->assertSessionHasErrors('attendance_date');
        $this->assertDatabaseCount('presences', 0);
    }

    #[Test]
    public function utilisateur_sans_permission_ne_peut_pas_enregistrer_une_presence(): void
    {
        $user = User::factory()->create();
        $inscription = $this->inscription();

        $this->actingAs($user)->get(route('presences.create'))->assertForbidden();
        $this->actingAs($user)->post(route('presences.store'), [
            'inscription_id' => $inscription->id,
            'attendance_date' => '2026-08-11',
            'status' => AttendanceStatus::Presente->value,
        ])->assertForbidden();

        $this->assertDatabaseCount('presences', 0);
    }

    #[Test]
    public function coach_peut_corriger_une_presence_et_l_audit_est_conserve(): void
    {
        $manager = $this->manager();
        $coach = User::factory()->create();
        $coach->assignRole('coach');
        $inscription = $this->inscription();
        $presence = Presence::factory()->create([
            'inscription_id' => $inscription->id,
            'attendance_date' => '2026-08-11',
            'status' => AttendanceStatus::Presente,
            'recorded_by' => $manager->id,
            'updated_by' => $manager->id,
        ]);

        $response = $this->actingAs($coach)->put(route('presences.update', $presence), [
            'inscription_id' => $inscription->id,
            'attendance_date' => '2026-08-11',
            'status' => AttendanceStatus::Absente->value,
            'comment' => 'Absence confirmée.',
        ]);

        $response->assertRedirect(route('presences.show', $presence));
        $presence->refresh();

        $this->assertSame(AttendanceStatus::Absente, $presence->status);
        $this->assertSame($manager->id, $presence->recorded_by);
        $this->assertSame($coach->id, $presence->updated_by);
        $this->assertSame('Absence confirmée.', $presence->comment);
    }

    #[Test]
    public function une_presence_sur_une_inscription_n_affecte_pas_l_autre_inscription_de_la_session(): void
    {
        $manager = $this->manager();
        $challenge = Challenge::factory()->create([
            'start_date' => '2026-08-01',
            'duration_days' => 30,
        ]);
        $firstInscription = Inscription::factory()->create(['challenge_id' => $challenge->id]);
        $secondInscription = Inscription::factory()->create(['challenge_id' => $challenge->id]);

        $this->actingAs($manager)->post(route('presences.store'), [
            'inscription_id' => $firstInscription->id,
            'attendance_date' => '2026-08-11',
            'status' => AttendanceStatus::Presente->value,
        ])->assertRedirect();

        $this->assertSame(1, $firstInscription->presences()->count());
        $this->assertSame(0, $secondInscription->presences()->count());
    }

    #[Test]
    public function manager_peut_pointer_toutes_les_participantes_d_une_session_en_une_seule_validation(): void
    {
        $manager = $this->manager();
        $challenge = Challenge::factory()->create([
            'start_date' => '2026-08-01',
            'duration_days' => 30,
        ]);
        $firstInscription = Inscription::factory()->create(['challenge_id' => $challenge->id]);
        $secondInscription = Inscription::factory()->create(['challenge_id' => $challenge->id]);

        $this->actingAs($manager)
            ->get(route('presences.bulk.create', [
                'challenge_id' => $challenge->id,
                'attendance_date' => '2026-08-11',
            ]))
            ->assertOk()
            ->assertSee('Pointage rapide')
            ->assertSee($firstInscription->participante->full_name)
            ->assertSee($secondInscription->participante->full_name);

        $response = $this->actingAs($manager)->post(
            route('presences.bulk.store'),
            $this->bulkPayload($challenge, [
                $firstInscription->id => AttendanceStatus::Presente,
                $secondInscription->id => AttendanceStatus::Absente,
            ])
        );

        $response->assertRedirect(route('presences.bulk.create', [
            'challenge_id' => $challenge->id,
            'attendance_date' => '2026-08-11',
        ]));
        $this->assertDatabaseCount('presences', 2);
        $this->assertDatabaseHas('presences', [
            'inscription_id' => $firstInscription->id,
            'attendance_date' => '2026-08-11',
            'status' => AttendanceStatus::Presente->value,
            'recorded_by' => $manager->id,
        ]);
        $this->assertDatabaseHas('presences', [
            'inscription_id' => $secondInscription->id,
            'attendance_date' => '2026-08-11',
            'status' => AttendanceStatus::Absente->value,
            'recorded_by' => $manager->id,
        ]);
    }

    #[Test]
    public function pointage_collectif_met_a_jour_une_presence_existante_sans_creer_de_doublon(): void
    {
        $manager = $this->manager();
        $challenge = Challenge::factory()->create([
            'start_date' => '2026-08-01',
            'duration_days' => 30,
        ]);
        $firstInscription = Inscription::factory()->create(['challenge_id' => $challenge->id]);
        $secondInscription = Inscription::factory()->create(['challenge_id' => $challenge->id]);
        $existingPresence = Presence::factory()->create([
            'inscription_id' => $firstInscription->id,
            'attendance_date' => '2026-08-11',
            'status' => AttendanceStatus::Presente,
            'recorded_by' => $manager->id,
            'updated_by' => $manager->id,
        ]);

        $this->actingAs($manager)->post(
            route('presences.bulk.store'),
            $this->bulkPayload($challenge, [
                $firstInscription->id => AttendanceStatus::Absente,
                $secondInscription->id => AttendanceStatus::Presente,
            ])
        )->assertRedirect();

        $this->assertSame(2, Presence::query()->count());
        $this->assertSame(AttendanceStatus::Absente, $existingPresence->fresh()->status);
        $this->assertSame($manager->id, $existingPresence->fresh()->recorded_by);
        $this->assertSame($manager->id, $existingPresence->fresh()->updated_by);
    }

    #[Test]
    public function pointage_collectif_valide_la_date_de_la_session(): void
    {
        $manager = $this->manager();
        $inscription = $this->inscription();

        $response = $this->actingAs($manager)
            ->from(route('presences.bulk.create', ['challenge_id' => $inscription->challenge_id]))
            ->post(route('presences.bulk.store'), [
                'challenge_id' => $inscription->challenge_id,
                'attendance_date' => '2026-09-15',
                'presences' => [
                    $inscription->id => ['status' => AttendanceStatus::Presente->value],
                ],
            ]);

        $response->assertRedirect(route('presences.bulk.create', ['challenge_id' => $inscription->challenge_id]));
        $response->assertSessionHasErrors('attendance_date');
        $this->assertDatabaseCount('presences', 0);
    }

    #[Test]
    public function utilisateur_sans_permission_ne_peut_pas_utiliser_le_pointage_collectif(): void
    {
        $user = User::factory()->create();
        $inscription = $this->inscription();

        $this->actingAs($user)
            ->get(route('presences.bulk.create', ['challenge_id' => $inscription->challenge_id]))
            ->assertForbidden();
        $this->actingAs($user)
            ->post(route('presences.bulk.store'), [
                'challenge_id' => $inscription->challenge_id,
                'attendance_date' => '2026-08-11',
                'presences' => [
                    $inscription->id => ['status' => AttendanceStatus::Presente->value],
                ],
            ])
            ->assertForbidden();
    }

    #[Test]
    public function fiche_participante_affiche_le_nombre_de_presences_de_son_inscription(): void
    {
        $manager = $this->manager();
        $participante = Participante::factory()->create();
        $inscription = $this->inscription($participante);

        Presence::factory()->create([
            'inscription_id' => $inscription->id,
            'attendance_date' => '2026-08-11',
            'status' => AttendanceStatus::Presente,
            'recorded_by' => $manager->id,
        ]);

        $response = $this->actingAs($manager)->get(route('participantes.show', $participante));

        $response->assertOk();
        $response->assertSee('1 présence(s)');
    }

    private function manager(): User
    {
        $manager = User::factory()->create();
        $manager->assignRole('manager');

        return $manager;
    }

    private function inscription(?Participante $participante = null): Inscription
    {
        $challenge = Challenge::factory()->create([
            'start_date' => '2026-08-01',
            'duration_days' => 30,
        ]);

        return Inscription::factory()->create([
            'participante_id' => $participante?->id ?? Participante::factory(),
            'challenge_id' => $challenge->id,
        ]);
    }

    /**
     * @param  array<int, AttendanceStatus>  $statuses
     */
    private function bulkPayload(Challenge $challenge, array $statuses): array
    {
        return [
            'challenge_id' => $challenge->id,
            'attendance_date' => '2026-08-11',
            'presences' => collect($statuses)
                ->map(fn (AttendanceStatus $status): array => ['status' => $status->value])
                ->all(),
        ];
    }
}
