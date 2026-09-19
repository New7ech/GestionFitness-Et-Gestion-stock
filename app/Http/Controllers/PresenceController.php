<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Http\Requests\StoreBulkPresenceRequest;
use App\Http\Requests\StorePresenceRequest;
use App\Http\Requests\UpdatePresenceRequest;
use App\Models\Challenge;
use App\Models\Inscription;
use App\Models\Presence;
use App\Services\PresenceService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PresenceController extends Controller
{
    public function __construct(private readonly PresenceService $presenceService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Presence::class);

        $presences = Presence::query()
            ->with(['inscription.participante', 'inscription.challenge.challengeType', 'recordedBy', 'updatedBy'])
            ->when($request->filled('inscription_id'), fn ($query) => $query->where('inscription_id', $request->integer('inscription_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('attendance_date', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('attendance_date', '<=', $request->input('date_to')))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = $request->string('q')->toString();
                $query->whereHas('inscription.participante', function ($nestedQuery) use ($term): void {
                    $nestedQuery
                        ->where('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('presences.index', [
            'presences' => $presences,
            'statuses' => AttendanceStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Presence::class);

        return view('presences.create', $this->formData(new Presence([
            'inscription_id' => $request->integer('inscription_id') ?: null,
            'attendance_date' => now()->toDateString(),
            'status' => AttendanceStatus::Presente,
        ])));
    }

    public function bulkCreate(Request $request): View
    {
        $this->authorize('create', Presence::class);

        $challenges = Challenge::query()
            ->with('challengeType')
            ->withCount('inscriptions')
            ->has('inscriptions')
            ->orderByDesc('start_date')
            ->get();
        $challenge = $challenges->firstWhere('id', $request->integer('challenge_id'));
        $attendanceDate = $request->date('attendance_date') ?? now();

        if ($challenge && ! $request->filled('attendance_date')) {
            $attendanceDate = $this->defaultAttendanceDate($challenge);
        }

        $inscriptions = $challenge
            ? $challenge->inscriptions()
                ->with([
                    'participante',
                    'presences' => fn ($query) => $query->whereDate('attendance_date', $attendanceDate->toDateString()),
                ])
                ->get()
                ->sortBy(fn (Inscription $inscription) => $inscription->participante->full_name)
                ->values()
            : collect();

        return view('presences.bulk-create', [
            'challenges' => $challenges,
            'challenge' => $challenge,
            'attendanceDate' => $attendanceDate,
            'inscriptions' => $inscriptions,
            'statuses' => AttendanceStatus::cases(),
        ]);
    }

    public function bulkStore(StoreBulkPresenceRequest $request): RedirectResponse
    {
        $this->authorize('create', Presence::class);

        $challenge = $request->challenge();
        $attendanceDate = $request->date('attendance_date')->toDateString();
        $entries = $request->validated('presences');
        $existingPresence = Presence::query()
            ->whereIn('inscription_id', array_keys($entries))
            ->whereDate('attendance_date', $attendanceDate)
            ->first();

        if ($existingPresence) {
            $this->authorize('update', $existingPresence);
        }

        $result = $this->presenceService->recordBulk(
            $challenge,
            $attendanceDate,
            $entries,
            $request->user()->id,
        );

        $message = "{$result['created']} présence(s) enregistrée(s)";

        if ($result['updated'] > 0) {
            $message .= ", {$result['updated']} mise(s) à jour";
        }

        if ($result['unchanged'] > 0) {
            $message .= ", {$result['unchanged']} déjà à jour";
        }

        return redirect()
            ->route('presences.bulk.create', [
                'challenge_id' => $challenge->id,
                'attendance_date' => $attendanceDate,
            ])
            ->with('success', "{$message}.");
    }

    public function store(StorePresenceRequest $request): RedirectResponse
    {
        $this->authorize('create', Presence::class);

        $presence = Presence::query()->create($request->validated() + [
            'recorded_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('presences.show', $presence)
            ->with('success', 'Présence enregistrée avec succès.');
    }

    public function show(Presence $presence): View
    {
        $this->authorize('view', $presence);

        $presence->load(['inscription.participante', 'inscription.challenge.challengeType', 'recordedBy', 'updatedBy']);

        return view('presences.show', compact('presence'));
    }

    public function edit(Presence $presence): View
    {
        $this->authorize('update', $presence);

        $presence->load(['inscription.participante', 'inscription.challenge.challengeType']);

        return view('presences.edit', $this->formData($presence) + [
            'lockedInscription' => true,
        ]);
    }

    public function update(UpdatePresenceRequest $request, Presence $presence): RedirectResponse
    {
        $this->authorize('update', $presence);

        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        unset($data['inscription_id']);

        $presence->update($data);

        return redirect()
            ->route('presences.show', $presence)
            ->with('success', 'Présence mise à jour avec succès.');
    }

    private function formData(Presence $presence): array
    {
        return [
            'presence' => $presence,
            'inscriptions' => Inscription::query()
                ->with(['participante', 'challenge.challengeType'])
                ->latest()
                ->get(),
            'statuses' => AttendanceStatus::cases(),
            'lockedInscription' => false,
        ];
    }

    private function defaultAttendanceDate(Challenge $challenge): Carbon
    {
        $today = now();

        if ($today->lt($challenge->start_date)) {
            return $challenge->start_date->copy();
        }

        if ($today->gt($challenge->end_date)) {
            return $challenge->end_date->copy();
        }

        return $today;
    }
}
