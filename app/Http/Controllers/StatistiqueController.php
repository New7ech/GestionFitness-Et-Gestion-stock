<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Enums\ChallengeStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\Challenge;
use App\Models\Mesure;
use App\Models\Paiement;
use App\Models\Participante;
use App\Models\Presence;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class StatistiqueController extends Controller
{
    public function index(): View
    {
        $today = Carbon::now();
        $start30Days = $today->copy()->subDays(29)->toDateString();
        $endToday = $today->toDateString();

        $totalParticipantes = Participante::query()->count();
        $totalChallenges = Challenge::query()->count();
        $challengesTermines = Challenge::query()
            ->where('status', ChallengeStatus::Termine->value)
            ->count();

        $tauxCompletion = $totalChallenges > 0
            ? round(($challengesTermines / $totalChallenges) * 100, 1)
            : 0.0;

        $totalPresences30Jours = Presence::query()
            ->whereBetween('attendance_date', [$start30Days, $endToday])
            ->count();

        $presentes30Jours = Presence::query()
            ->whereBetween('attendance_date', [$start30Days, $endToday])
            ->where('status', AttendanceStatus::Presente->value)
            ->count();

        $tauxPresence30Jours = $totalPresences30Jours > 0
            ? round(($presentes30Jours / $totalPresences30Jours) * 100, 1)
            : 0.0;

        $paiements30Jours = (float) Paiement::query()
            ->whereBetween('payment_date', [$start30Days, $endToday])
            ->where('type', PaymentType::Paiement->value)
            ->sum('amount');

        $remboursements30Jours = (float) Paiement::query()
            ->whereBetween('payment_date', [$start30Days, $endToday])
            ->where('type', PaymentType::Remboursement->value)
            ->sum('amount');

        $revenuNet30Jours = $paiements30Jours - $remboursements30Jours;

        $statusChallenges = collect(ChallengeStatus::cases())
            ->map(fn (ChallengeStatus $status) => [
                'label' => $status->label(),
                'value' => $status->value,
                'total' => Challenge::query()->where('status', $status->value)->count(),
            ])
            ->filter(fn (array $status) => $status['total'] > 0)
            ->values();

        $statusPaiements = collect(PaymentStatus::cases())
            ->map(fn (PaymentStatus $status) => [
                'label' => $status->label(),
                'value' => $status->value,
                'total' => Challenge::query()->where('payment_status', $status->value)->count(),
            ])
            ->filter(fn (array $status) => $status['total'] > 0)
            ->values();

        $mesuresParJour = Mesure::query()
            ->selectRaw('DATE(measured_at) as measured_day, AVG(weight) as average_weight, AVG(waist) as average_waist')
            ->whereBetween('measured_at', [$start30Days, $endToday])
            ->groupByRaw('DATE(measured_at)')
            ->orderBy('measured_day')
            ->get();

        $mesuresEvolution = [
            'labels' => $mesuresParJour
                ->pluck('measured_day')
                ->map(fn ($date) => Carbon::parse($date)->format('d/m'))
                ->toArray(),
            'weight' => $mesuresParJour
                ->pluck('average_weight')
                ->map(fn ($value) => round((float) $value, 2))
                ->toArray(),
            'waist' => $mesuresParJour
                ->pluck('average_waist')
                ->map(fn ($value) => $value === null ? null : round((float) $value, 2))
                ->toArray(),
        ];

        $presences = Presence::query()
            ->whereBetween('attendance_date', [$start30Days, $endToday])
            ->get(['attendance_date', 'status'])
            ->groupBy(fn (Presence $presence) => $presence->attendance_date->toDateString());

        $presenceEvolution = [
            'labels' => [],
            'data' => [],
        ];

        for ($i = 29; $i >= 0; $i--) {
            $day = $today->copy()->subDays($i);
            $rows = $presences->get($day->toDateString(), collect());
            $presentes = $rows->filter(fn (Presence $presence) => $presence->status === AttendanceStatus::Presente)->count();
            $total = $rows->count();

            $presenceEvolution['labels'][] = $day->format('d/m');
            $presenceEvolution['data'][] = $total > 0 ? round(($presentes / $total) * 100, 1) : 0;
        }

        $revenusParType = Paiement::query()
            ->with('challenge.challengeType')
            ->whereBetween('payment_date', [$start30Days, $endToday])
            ->get()
            ->groupBy(fn (Paiement $paiement) => $paiement->challenge?->challengeType?->label ?? 'Sans type')
            ->map(function ($paiements, string $label): array {
                $paiementsBruts = $paiements
                    ->where('type', PaymentType::Paiement)
                    ->sum(fn (Paiement $paiement) => (float) $paiement->amount);
                $remboursements = $paiements
                    ->where('type', PaymentType::Remboursement)
                    ->sum(fn (Paiement $paiement) => (float) $paiement->amount);

                return [
                    'label' => $label,
                    'total' => $paiementsBruts - $remboursements,
                ];
            })
            ->sortByDesc('total')
            ->values();

        $completionParType = Challenge::query()
            ->with('challengeType')
            ->get()
            ->groupBy(fn (Challenge $challenge) => $challenge->challengeType?->label ?? 'Sans type')
            ->map(function ($challenges, string $label): array {
                $total = $challenges->count();
                $termines = $challenges
                    ->where('status', ChallengeStatus::Termine)
                    ->count();

                return [
                    'label' => $label,
                    'total' => $total,
                    'termines' => $termines,
                    'taux' => $total > 0 ? round(($termines / $total) * 100, 1) : 0.0,
                ];
            })
            ->sortByDesc('total')
            ->values();

        $mesuresRecentes = Mesure::query()
            ->with(['challenge.participante', 'challenge.challengeType'])
            ->orderByDesc('measured_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('statistiques.index', [
            'totalParticipantes' => $totalParticipantes,
            'totalChallenges' => $totalChallenges,
            'tauxCompletion' => $tauxCompletion,
            'tauxPresence30Jours' => $tauxPresence30Jours,
            'revenuNet30Jours' => $revenuNet30Jours,
            'statusChallenges' => $statusChallenges,
            'statusPaiements' => $statusPaiements,
            'mesuresEvolution' => $mesuresEvolution,
            'presenceEvolution' => $presenceEvolution,
            'revenusParType' => $revenusParType,
            'completionParType' => $completionParType,
            'mesuresRecentes' => $mesuresRecentes,
        ]);
    }
}
