<?php

namespace Tests\Feature;

use App\Enums\ChallengeStatus;
use App\Enums\PaymentMode;
use App\Enums\PaymentType;
use App\Models\Challenge;
use App\Models\ChallengeType;
use App\Models\Inscription;
use App\Models\Paiement;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\FitnessReferenceSeeder;
use Database\Seeders\ImproveRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DashboardStatisticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ImproveRolesAndPermissionsSeeder::class);
        $this->seed(FitnessReferenceSeeder::class);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Carbon::setTestNow(Carbon::parse('2026-09-18 10:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    #[Test]
    public function repartition_par_programme_compte_les_inscriptions_et_ventile_les_montants_nets(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('manager');

        $perteDePoids = ChallengeType::query()->where('code', 'perte_de_poids')->firstOrFail();
        $diastasis = ChallengeType::query()->where('code', 'diastasis')->firstOrFail();
        $sessionPartagee = Challenge::factory()->create([
            'challenge_type_id' => $perteDePoids->id,
            'start_date' => '2026-09-10',
        ]);
        $sessionDiastasis = Challenge::factory()->create([
            'challenge_type_id' => $diastasis->id,
            'start_date' => '2026-09-18',
        ]);

        $premiereInscription = Inscription::factory()->create([
            'challenge_id' => $sessionPartagee->id,
            'status' => ChallengeStatus::EnCours,
            'price' => 50000,
        ]);
        $secondeInscription = Inscription::factory()->create([
            'challenge_id' => $sessionPartagee->id,
            'status' => ChallengeStatus::EnCours,
            'price' => 70000,
        ]);
        $inscriptionDiastasis = Inscription::factory()->create([
            'challenge_id' => $sessionDiastasis->id,
            'status' => ChallengeStatus::Planifie,
            'price' => 40000,
        ]);

        $this->payment($premiereInscription, $manager, 30000, PaymentType::Paiement, '2026-09-10');
        $this->payment($premiereInscription, $manager, 5000, PaymentType::Remboursement, '2026-09-12');
        $this->payment($secondeInscription, $manager, 70000, PaymentType::Paiement, '2026-09-16');
        $this->payment($inscriptionDiastasis, $manager, 10000, PaymentType::Paiement, '2026-09-18');

        $response = $this->actingAs($manager)->get(route('accueil'));

        $response
            ->assertOk()
            ->assertSee('Répartition par programme')
            ->assertViewHas('chiffreAffairesEncaisse', 105000.0)
            ->assertViewHas('resteAPercevoir', 55000.0)
            ->assertViewHas('programDistribution', function (Collection $programs): bool {
                $perteDePoids = $programs->firstWhere('label', 'Perte de poids');
                $diastasis = $programs->firstWhere('label', 'Rééducation de la diastasie');

                return $perteDePoids === [
                    'label' => 'Perte de poids',
                    'inscriptions_count' => 2,
                    'net_collected' => 95000.0,
                    'outstanding' => 25000.0,
                    'collected_first_half' => 25000.0,
                    'collected_second_half' => 70000.0,
                    'starts_first_half' => 2,
                    'starts_second_half' => 0,
                ] && $diastasis === [
                    'label' => 'Rééducation de la diastasie',
                    'inscriptions_count' => 1,
                    'net_collected' => 10000.0,
                    'outstanding' => 30000.0,
                    'collected_first_half' => 0.0,
                    'collected_second_half' => 10000.0,
                    'starts_first_half' => 0,
                    'starts_second_half' => 1,
                ];
            });
    }

    private function payment(
        Inscription $inscription,
        User $recordedBy,
        float $amount,
        PaymentType $type,
        string $date,
    ): void {
        Paiement::query()->create([
            'inscription_id' => $inscription->id,
            'amount' => $amount,
            'type' => $type,
            'payment_date' => $date,
            'payment_mode' => PaymentMode::Especes,
            'recorded_by' => $recordedBy->id,
        ]);
    }
}
