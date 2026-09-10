<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChallengeRequest;
use App\Http\Requests\UpdateChallengeRequest;
use App\Models\Challenge;
use App\Models\ChallengeType;
use App\Services\InscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    public function __construct(private readonly InscriptionService $inscriptionService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Challenge::class);

        $challenges = Challenge::query()
            ->with('challengeType')
            ->withCount('inscriptions')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = $request->string('q')->toString();
                $query->where(function ($searchQuery) use ($term): void {
                    $searchQuery
                        ->whereHas('challengeType', fn ($typeQuery) => $typeQuery->where('label', 'like', "%{$term}%"))
                        ->orWhereHas('inscriptions.participante', function ($participanteQuery) use ($term): void {
                            $participanteQuery
                                ->where('first_name', 'like', "%{$term}%")
                                ->orWhere('last_name', 'like', "%{$term}%")
                                ->orWhere('phone', 'like', "%{$term}%");
                        });
                });
            })
            ->when($request->filled('challenge_type_id'), fn ($query) => $query->where('challenge_type_id', $request->integer('challenge_type_id')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('challenges.index', [
            'challenges' => $challenges,
            'challengeTypes' => ChallengeType::query()->orderBy('label')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Challenge::class);

        return view('challenges.create', $this->formData(new Challenge([
            'start_date' => now()->toDateString(),
            'duration_days' => config('fitness.durations', [15, 30])[0],
        ])));
    }

    public function store(StoreChallengeRequest $request): RedirectResponse
    {
        $this->authorize('create', Challenge::class);

        $challenge = $this->inscriptionService->createChallenge($request->validated(), $request->user()->id);

        return redirect()
            ->route('challenges.show', $challenge)
            ->with('success', 'Session créée avec succès. Vous pouvez maintenant y inscrire des participantes.');
    }

    public function show(Challenge $challenge): View
    {
        $this->authorize('view', $challenge);

        $challenge->load([
            'challengeType',
            'createdBy',
            'updatedBy',
            'inscriptions' => fn ($query) => $query
                ->with([
                    'participante',
                    'paiements.recu',
                    'presences.recordedBy',
                    'presences.updatedBy',
                    'mesures.values.measurementType',
                    'mesures.media.uploadedBy',
                    'media.uploadedBy',
                ])
                ->latest(),
        ]);

        return view('challenges.show', [
            'challenge' => $challenge,
            'hasHistoricalData' => $this->hasHistoricalData($challenge),
        ]);
    }

    public function edit(Challenge $challenge): View
    {
        $this->authorize('update', $challenge);

        return view('challenges.edit', $this->formData($challenge));
    }

    public function update(UpdateChallengeRequest $request, Challenge $challenge): RedirectResponse
    {
        $this->authorize('update', $challenge);

        if ($this->scheduleWillChange($request, $challenge) && $this->hasHistoricalData($challenge) && ! $request->boolean('confirm_schedule_change')) {
            return back()
                ->withInput()
                ->with('warning', 'Cette session contient des inscriptions avec des paiements, présences ou mesures. Cochez la confirmation pour modifier le planning.');
        }

        $challenge->update($request->safe()->except('confirm_schedule_change') + [
            'updated_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('challenges.show', $challenge)
            ->with('success', 'Session mise à jour avec succès.');
    }

    public function destroy(Challenge $challenge): RedirectResponse
    {
        $this->authorize('delete', $challenge);

        // Règle stricte : une session reste conservée dès qu'une inscription existe,
        // même si cette inscription ne possède encore aucune donnée datée.
        if ($challenge->inscriptions()->exists()) {
            return redirect()
                ->route('challenges.show', $challenge)
                ->with('error', 'Impossible de supprimer une session dès lors qu’au moins une inscription y est rattachée.');
        }

        $challenge->delete();

        return redirect()
            ->route('challenges.index')
            ->with('success', 'Session supprimée avec succès.');
    }

    private function formData(Challenge $challenge): array
    {
        return [
            'challenge' => $challenge,
            'challengeTypes' => ChallengeType::query()->where('is_active', true)->orderBy('label')->get(),
            'durations' => config('fitness.durations', [15, 30]),
        ];
    }

    private function scheduleWillChange(Request $request, Challenge $challenge): bool
    {
        return $request->date('start_date')?->toDateString() !== $challenge->start_date->toDateString()
            || (int) $request->input('duration_days') !== (int) $challenge->duration_days;
    }

    private function hasHistoricalData(Challenge $challenge): bool
    {
        return $challenge->inscriptions()
            ->where(function ($query): void {
                $query
                    ->whereHas('paiements')
                    ->orWhereHas('presences')
                    ->orWhereHas('mesures');
            })
            ->exists();
    }
}
