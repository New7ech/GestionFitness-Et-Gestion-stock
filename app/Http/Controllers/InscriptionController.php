<?php

namespace App\Http\Controllers;

use App\Enums\ChallengeStatus;
use App\Enums\ParticipantStatus;
use App\Exceptions\DuplicateParticipantePhoneException;
use App\Http\Requests\StoreInscriptionRequest;
use App\Http\Requests\UpdateInscriptionRequest;
use App\Models\Challenge;
use App\Models\ChallengeType;
use App\Models\Inscription;
use App\Models\Participante;
use App\Services\InscriptionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InscriptionController extends Controller
{
    public function __construct(private readonly InscriptionService $inscriptionService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Inscription::class);

        $inscriptions = Inscription::query()
            ->with(['participante', 'challenge.challengeType'])
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = $request->string('q')->toString();
                $query->whereHas('participante', function ($participanteQuery) use ($term): void {
                    $participanteQuery
                        ->where('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('challenge_id'), fn ($query) => $query->where('challenge_id', $request->integer('challenge_id')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('inscriptions.index', [
            'inscriptions' => $inscriptions,
            'statuses' => ChallengeStatus::cases(),
            'challenges' => Challenge::query()->with('challengeType')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Inscription::class);

        return view('inscriptions.create', $this->createFormData());
    }

    public function store(StoreInscriptionRequest $request): RedirectResponse
    {
        $this->authorize('create', Inscription::class);

        try {
            $inscription = $this->inscriptionService->inscrire(
                $request->string('participante_mode')->toString(),
                $request->integer('participante_id') ?: null,
                $request->participanteData(),
                $request->string('challenge_mode')->toString(),
                $request->integer('challenge_id') ?: null,
                $request->challengeData(),
                $request->inscriptionData(),
                $request->user()->id,
                $request->file('participante.photo'),
                $request->confirmDuplicatePhone()
            );
        } catch (DuplicateParticipantePhoneException $exception) {
            return back()
                ->withInput()
                ->with('warning', "Le téléphone existe déjà pour {$exception->participante->full_name}. Cochez la confirmation pour continuer.");
        }

        return redirect()
            ->route('inscriptions.show', $inscription)
            ->with('success', 'Inscription créée avec succès.');
    }

    public function show(Inscription $inscription): View
    {
        $this->authorize('view', $inscription);

        $inscription->load([
            'participante',
            'challenge.challengeType',
            'paiements.recu',
            'presences.recordedBy',
            'presences.updatedBy',
            'mesures.values.measurementType',
            'mesures.recordedBy',
            'mesures.media.uploadedBy',
            'media.uploadedBy',
            'createdBy',
            'updatedBy',
        ]);

        return view('inscriptions.show', compact('inscription'));
    }

    public function edit(Inscription $inscription): View
    {
        $this->authorize('update', $inscription);

        $inscription->load(['participante', 'challenge.challengeType']);

        return view('inscriptions.edit', $this->editFormData($inscription));
    }

    public function update(UpdateInscriptionRequest $request, Inscription $inscription): RedirectResponse
    {
        $this->authorize('update', $inscription);

        $inscription->update($request->validated() + [
            'updated_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('inscriptions.show', $inscription)
            ->with('success', 'Inscription mise à jour avec succès.');
    }

    public function destroy(Inscription $inscription): RedirectResponse
    {
        $this->authorize('delete', $inscription);

        if ($this->hasHistoricalData($inscription)) {
            return redirect()
                ->route('inscriptions.show', $inscription)
                ->with('error', 'Impossible de supprimer une inscription qui possède des paiements, présences ou mesures.');
        }

        $challenge = $inscription->challenge;
        $inscription->delete();

        return redirect()
            ->route('challenges.show', $challenge)
            ->with('success', 'Inscription supprimée avec succès.');
    }

    public function changeStatus(Request $request, Inscription $inscription): RedirectResponse
    {
        $this->authorize('changeStatus', $inscription);

        $data = $request->validate([
            'status' => ['required', Rule::enum(ChallengeStatus::class)],
        ], [
            'status.required' => 'Le statut est obligatoire.',
            'status' => 'Le statut sélectionné est invalide.',
        ]);

        $inscription->update([
            'status' => $data['status'],
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Statut de l’inscription mis à jour.');
    }

    private function createFormData(): array
    {
        return [
            'participantes' => Participante::query()->orderBy('last_name')->orderBy('first_name')->get(),
            'challenges' => Challenge::query()->with('challengeType')->latest()->get(),
            'challengeTypes' => ChallengeType::query()->where('is_active', true)->orderBy('label')->get(),
            'participantStatuses' => ParticipantStatus::cases(),
            'inscriptionStatuses' => ChallengeStatus::cases(),
            'durations' => config('fitness.durations', [15, 30]),
        ];
    }

    private function editFormData(Inscription $inscription): array
    {
        return [
            'inscription' => $inscription,
            'participantes' => Participante::query()->orderBy('last_name')->orderBy('first_name')->get(),
            'challenges' => Challenge::query()->with('challengeType')->latest()->get(),
            'inscriptionStatuses' => ChallengeStatus::cases(),
            'associationLocked' => $this->hasHistoricalData($inscription),
        ];
    }

    private function hasHistoricalData(Inscription $inscription): bool
    {
        return $inscription->paiements()->exists()
            || $inscription->presences()->exists()
            || $inscription->mesures()->exists();
    }
}
