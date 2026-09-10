<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Enums\ChallengeStatus;
use App\Enums\ParticipantStatus;
use App\Enums\PaymentMode;
use App\Enums\PaymentType;
use App\Models\Challenge;
use App\Models\Inscription;
use App\Models\Paiement;
use App\Models\Participante;
use App\Models\Presence;
use App\Models\Recu;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class AccueilController extends Controller
{
    public function index(): View
    {
        $now = Carbon::now();
        $today = $now->toDateString();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $participantesActives = Participante::query()
            ->where('status', ParticipantStatus::Active->value)
            ->count();

        $challengesEnCours = Inscription::query()
            ->where('status', ChallengeStatus::EnCours->value)
            ->count();

        $challengesPlanifies = Inscription::query()
            ->where('status', ChallengeStatus::Planifie->value)
            ->count();

        $challengesTermines = Inscription::query()
            ->where('status', ChallengeStatus::Termine->value)
            ->count();

        $paiementsMois = (float) Paiement::query()
            ->where('type', PaymentType::Paiement->value)
            ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $remboursementsMois = (float) Paiement::query()
            ->where('type', PaymentType::Remboursement->value)
            ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $chiffreAffairesMois = $paiementsMois - $remboursementsMois;

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

        $challengesParType = Inscription::query()
            ->join('challenges', 'inscriptions.challenge_id', '=', 'challenges.id')
            ->join('challenge_types', 'challenges.challenge_type_id', '=', 'challenge_types.id')
            ->selectRaw('challenge_types.label as label, COUNT(*) as total')
            ->groupBy('challenge_types.label')
            ->orderByDesc('total')
            ->get();

        return view('accueil.index', [
            'participantesActives' => $participantesActives,
            'challengesEnCours' => $challengesEnCours,
            'challengesPlanifies' => $challengesPlanifies,
            'challengesTermines' => $challengesTermines,
            'chiffreAffairesMois' => $chiffreAffairesMois,
            'presencesDuJour' => $presencesDuJour,
            'presentesDuJour' => $presentesDuJour,
            'absentesDuJour' => $absentesDuJour,
            'recusRecents' => $recusRecents,
            'challengesACloturer' => $challengesACloturer,
            'challengesRecents' => $challengesRecents,
            'paiementsParMode' => $paiementsParMode,
            'challengesParType' => $challengesParType,
            'revenusJournalier' => $this->revenusJournalierSur7Jours(),
        ]);
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
