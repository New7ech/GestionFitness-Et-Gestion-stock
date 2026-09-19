<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Enums\ChallengeStatus;
use App\Enums\PaymentMode;
use App\Enums\PaymentType;
use App\Models\Challenge;
use App\Models\ChallengeType;
use App\Models\Inscription;
use App\Models\Paiement;
use App\Models\Presence;
use App\Models\Recu;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class AccueilController extends Controller
{
    public function index(): View
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $endOfFirstHalf = $startOfMonth->copy()->addDays(13);
        $startOfSecondHalf = $startOfMonth->copy()->addDays(14);

        $challengesEnCours = Inscription::query()
            ->where('status', ChallengeStatus::EnCours->value)
            ->count();

        $challengesPlanifies = Inscription::query()
            ->where('status', ChallengeStatus::Planifie->value)
            ->count();

        $challengesTermines = Inscription::query()
            ->where('status', ChallengeStatus::Termine->value)
            ->count();

        $demarragesMois = Inscription::query()
            ->whereHas('challenge', function ($query) use ($startOfMonth, $endOfMonth): void {
                $query->whereBetween('start_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);
            })
            ->count();

        $inscriptions = Inscription::query()
            ->with([
                'challenge:id,challenge_type_id,start_date',
                'challenge.challengeType:id,label',
                'paiements:id,inscription_id,amount,type,payment_date',
                'mesures:id,inscription_id,measured_at',
            ])
            ->get();

        $paiements = $inscriptions->flatMap(fn (Inscription $inscription) => $inscription->paiements);
        $chiffreAffairesEncaisse = $this->netPayments($paiements);
        $chiffreAffairesMois = $this->netPaymentsForPeriod($paiements, $startOfMonth, $endOfMonth);
        $caPremiereQuinzaine = $this->netPaymentsForPeriod($paiements, $startOfMonth, $endOfFirstHalf);
        $caSecondeQuinzaine = $this->netPaymentsForPeriod($paiements, $startOfSecondHalf, $endOfMonth);
        $resteAPercevoir = $inscriptions->sum(function (Inscription $inscription): float {
            return max(0, (float) $inscription->price - $this->netPayments($inscription->paiements));
        });

        $presencesEnregistrees = Presence::query()->count();
        $tauxPresenceEnregistre = $presencesEnregistrees > 0
            ? round((Presence::query()->where('status', AttendanceStatus::Presente->value)->count() / $presencesEnregistrees) * 100, 1)
            : null;

        $presencesDuJour = Presence::query()
            ->whereDate('attendance_date', $today)
            ->count();

        $presentesDuJour = Presence::query()
            ->whereDate('attendance_date', $today)
            ->where('status', AttendanceStatus::Presente->value)
            ->count();

        $absentesDuJour = Presence::query()
            ->whereDate('attendance_date', $today)
            ->where('status', AttendanceStatus::Absente->value)
            ->count();

        $recusRecents = Recu::query()
            ->latest('issued_at')
            ->limit(5)
            ->get();

        $challengesACloturer = Inscription::query()
            ->with(['participante', 'challenge.challengeType'])
            ->where('status', ChallengeStatus::EnCours->value)
            ->whereHas('challenge', function ($query) use ($today, $now): void {
                $query->whereBetween('end_date', [$today, $now->copy()->addDays(7)->toDateString()]);
            })
            ->orderBy(
                Challenge::query()
                    ->select('end_date')
                    ->whereColumn('challenges.id', 'inscriptions.challenge_id')
            )
            ->limit(5)
            ->get();

        $challengesACloturerTotal = Inscription::query()
            ->where('status', ChallengeStatus::EnCours->value)
            ->whereHas('challenge', function ($query) use ($today, $now): void {
                $query->whereBetween('end_date', [$today, $now->copy()->addDays(7)->toDateString()]);
            })
            ->count();

        $retardsDeSuivi = $inscriptions
            ->filter(fn (Inscription $inscription) => $inscription->status === ChallengeStatus::EnCours)
            ->filter(function (Inscription $inscription) use ($now): bool {
                $derniereMesure = $inscription->mesures
                    ->sortByDesc('measured_at')
                    ->first()?->measured_at;

                return $derniereMesure === null || $derniereMesure->lt($now->copy()->subDays(35));
            })
            ->count();

        $challengesRecents = Inscription::query()
            ->with(['participante', 'challenge.challengeType'])
            ->whereIn('status', [ChallengeStatus::EnCours->value, ChallengeStatus::Planifie->value])
            ->latest()
            ->limit(6)
            ->get();

        $paiementsParMode = Paiement::query()
            ->selectRaw('payment_mode, COUNT(*) as count, SUM(amount) as total')
            ->where('type', PaymentType::Paiement->value)
            ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
            ->groupBy('payment_mode')
            ->orderByDesc('total')
            ->get()
            ->map(function ($mode): array {
                $paymentMode = $mode->payment_mode instanceof PaymentMode
                    ? $mode->payment_mode
                    : PaymentMode::tryFrom((string) $mode->payment_mode);

                return [
                    'label' => $paymentMode?->label() ?? (string) ($mode->getRawOriginal('payment_mode') ?? ''),
                    'count' => (int) $mode->count,
                    'total' => (float) $mode->total,
                ];
            });

        $programDistribution = $this->programDistribution(
            $inscriptions,
            ChallengeType::query()->orderBy('label')->get(['id', 'label']),
            $startOfMonth,
            $endOfFirstHalf,
            $startOfSecondHalf,
            $endOfMonth,
        );

        return view('accueil.index', [
            'challengesEnCours' => $challengesEnCours,
            'challengesPlanifies' => $challengesPlanifies,
            'challengesTermines' => $challengesTermines,
            'demarragesMois' => $demarragesMois,
            'chiffreAffairesEncaisse' => $chiffreAffairesEncaisse,
            'chiffreAffairesMois' => $chiffreAffairesMois,
            'caPremiereQuinzaine' => $caPremiereQuinzaine,
            'caSecondeQuinzaine' => $caSecondeQuinzaine,
            'resteAPercevoir' => $resteAPercevoir,
            'tauxPresenceEnregistre' => $tauxPresenceEnregistre,
            'presencesDuJour' => $presencesDuJour,
            'presentesDuJour' => $presentesDuJour,
            'absentesDuJour' => $absentesDuJour,
            'recusRecents' => $recusRecents,
            'challengesACloturer' => $challengesACloturer,
            'challengesACloturerTotal' => $challengesACloturerTotal,
            'retardsDeSuivi' => $retardsDeSuivi,
            'challengesRecents' => $challengesRecents,
            'paiementsParMode' => $paiementsParMode,
            'programDistribution' => $programDistribution,
            'revenusJournalier' => $this->revenusJournalierSur7Jours(),
        ]);
    }

    /**
     * @param  Collection<int, Paiement>  $paiements
     */
    private function netPayments(Collection $paiements): float
    {
        return (float) $paiements->sum(function (Paiement $paiement): float {
            return (float) $paiement->amount * ($paiement->type === PaymentType::Remboursement ? -1 : 1);
        });
    }

    /**
     * @param  Collection<int, Paiement>  $paiements
     */
    private function netPaymentsForPeriod(Collection $paiements, Carbon $start, Carbon $end): float
    {
        return $this->netPayments(
            $paiements->filter(function (Paiement $paiement) use ($start, $end): bool {
                return $paiement->payment_date !== null
                    && $paiement->payment_date->betweenIncluded($start, $end);
            })
        );
    }

    /**
     * @param  Collection<int, Inscription>  $inscriptions
     * @param  Collection<int, ChallengeType>  $challengeTypes
     * @return Collection<int, array{label: string, inscriptions_count: int, net_collected: float, outstanding: float, collected_first_half: float, collected_second_half: float, starts_first_half: int, starts_second_half: int}>
     */
    private function programDistribution(
        Collection $inscriptions,
        Collection $challengeTypes,
        Carbon $startOfMonth,
        Carbon $endOfFirstHalf,
        Carbon $startOfSecondHalf,
        Carbon $endOfMonth,
    ): Collection {
        $inscriptionsByType = $inscriptions->groupBy(
            fn (Inscription $inscription) => $inscription->challenge?->challenge_type_id
        );

        return $challengeTypes
            ->map(function (ChallengeType $challengeType) use (
                $inscriptionsByType,
                $startOfMonth,
                $endOfFirstHalf,
                $startOfSecondHalf,
                $endOfMonth,
            ): array {
                /** @var Collection<int, Inscription> $programInscriptions */
                $programInscriptions = $inscriptionsByType->get($challengeType->id, collect());
                $programPayments = $programInscriptions->flatMap(
                    fn (Inscription $inscription) => $inscription->paiements
                );

                return [
                    'label' => $challengeType->label,
                    'inscriptions_count' => $programInscriptions->count(),
                    'net_collected' => $this->netPayments($programPayments),
                    'outstanding' => (float) $programInscriptions->sum(
                        fn (Inscription $inscription): float => max(
                            0,
                            (float) $inscription->price - $this->netPayments($inscription->paiements)
                        )
                    ),
                    'collected_first_half' => $this->netPaymentsForPeriod(
                        $programPayments,
                        $startOfMonth,
                        $endOfFirstHalf
                    ),
                    'collected_second_half' => $this->netPaymentsForPeriod(
                        $programPayments,
                        $startOfSecondHalf,
                        $endOfMonth
                    ),
                    'starts_first_half' => $programInscriptions
                        ->filter(function (Inscription $inscription) use ($startOfMonth, $endOfFirstHalf): bool {
                            return $inscription->challenge?->start_date?->betweenIncluded($startOfMonth, $endOfFirstHalf) ?? false;
                        })
                        ->count(),
                    'starts_second_half' => $programInscriptions
                        ->filter(function (Inscription $inscription) use ($startOfSecondHalf, $endOfMonth): bool {
                            return $inscription->challenge?->start_date?->betweenIncluded($startOfSecondHalf, $endOfMonth) ?? false;
                        })
                        ->count(),
                ];
            })
            ->sortByDesc('inscriptions_count')
            ->values();
    }

    private function revenusJournalierSur7Jours(): array
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::now()->subDays($i);
            $labels[] = $day->translatedFormat('D d/m');

            $paiements = (float) Paiement::query()
                ->where('type', PaymentType::Paiement->value)
                ->whereDate('payment_date', $day->toDateString())
                ->sum('amount');

            $remboursements = (float) Paiement::query()
                ->where('type', PaymentType::Remboursement->value)
                ->whereDate('payment_date', $day->toDateString())
                ->sum('amount');

            $data[] = $paiements - $remboursements;
        }

        return compact('labels', 'data');
    }
}
