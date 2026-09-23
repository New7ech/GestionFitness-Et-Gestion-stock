@extends('layouts.app')

@section('title', 'Pointage collectif')

@section('contenus')
<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Pointage collectif</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('accueil') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('presences.index') }}">Présences</a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="#">Pointage collectif</a></li>
        </ul>
    </div>

    <div class="card card-round">
        <div class="card-header">
            <h4 class="card-title">Choisir la session et la date</h4>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('presences.bulk.create') }}" class="row g-3 align-items-end">
                <div class="col-md-7">
                    <label for="challenge_id" class="form-label">Session</label>
                    <select name="challenge_id" id="challenge_id" class="form-select" required>
                        <option value="">-- Choisir une session --</option>
                        @foreach($challenges as $availableChallenge)
                            <option value="{{ $availableChallenge->id }}" @selected($challenge?->is($availableChallenge))>
                                {{ $availableChallenge->challengeType->label }} — {{ $availableChallenge->start_date->format('d/m/Y') }} au {{ $availableChallenge->end_date->format('d/m/Y') }} ({{ $availableChallenge->inscriptions_count }} inscrite(s))
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="attendance_date" class="form-label">Date</label>
                    <input type="date" name="attendance_date" id="attendance_date" class="form-control" value="{{ $attendanceDate->format('Y-m-d') }}" required>
                    @error('attendance_date')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary"><i class="fas fa-users me-1"></i> Afficher</button>
                </div>
            </form>
        </div>
    </div>

    @if($challenge)
        <div class="card card-round">
            <div class="card-header d-flex flex-column flex-md-row align-items-md-center gap-2">
                <div>
                    <h4 class="card-title mb-1">{{ $challenge->challengeType->label }}</h4>
                    <div class="text-muted">{{ $attendanceDate->format('d/m/Y') }} · {{ $inscriptions->count() }} participante(s)</div>
                </div>
                <div class="ms-md-auto d-flex gap-2">
                    <button type="button" class="btn btn-outline-success btn-sm" id="markAllPresent"><i class="fas fa-check-double me-1"></i> Toutes présentes</button>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="markAllAbsent"><i class="fas fa-user-times me-1"></i> Toutes absentes</button>
                </div>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <strong>Le pointage n’a pas été enregistré.</strong> Veuillez corriger les erreurs signalées.
                    </div>
                @endif

                @if($inscriptions->isEmpty())
                    <div class="text-center text-muted py-4">Aucune participante n’est inscrite à cette session.</div>
                @else
                    <div class="alert alert-info d-flex align-items-start" role="alert">
                        <i class="fas fa-info-circle mt-1 me-2"></i>
                         <div>
                            <strong>Pointage rapide :</strong> toutes les participantes sont marquées <strong>présentes</strong> par défaut.
                            Basculez seulement les absences, puis enregistrez la feuille entière.
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-5 ms-md-auto">
                            <label for="participantSearch" class="visually-hidden">Rechercher une participante</label>
                            <input type="search" id="participantSearch" class="form-control" placeholder="Rechercher une participante…">
                        </div>
                    </div>

                    <form method="POST" action="{{ route('presences.bulk.store') }}">
                        @csrf
                        <input type="hidden" name="challenge_id" value="{{ $challenge->id }}">
                        <input type="hidden" name="attendance_date" value="{{ $attendanceDate->format('Y-m-d') }}">

                        <div class="table-responsive">
                            <table class="table table-hover align-items-center" id="attendanceTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Participante</th>
                                        <th class="text-center">Présente</th>
                                        <th class="text-center">Absente</th>
                                        <th class="text-end">État</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($inscriptions as $inscription)
                                        @php
                                            $existingPresence = $inscription->presences->first();
                                            $selectedStatus = old(
                                                "presences.{$inscription->id}.status",
                                                $existingPresence?->status?->value ?? \App\Enums\AttendanceStatus::Presente->value
                                            );
                                        @endphp
                                        <tr class="attendance-row" data-participante="{{ \Illuminate\Support\Str::lower($inscription->participante->full_name) }}">
                                            <td>
                                                <div class="fw-bold">{{ $inscription->participante->full_name }}</div>
                                                <small class="text-muted">{{ $inscription->participante->phone ?: 'Sans téléphone' }}</small>
                                                @error("presences.{$inscription->id}")
                                                    <div class="text-danger small">{{ $message }}</div>
                                                @enderror
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-check-inline m-0">
                                                    <input
                                                        class="form-check-input attendance-status"
                                                        type="radio"
                                                        name="presences[{{ $inscription->id }}][status]"
                                                        id="present-{{ $inscription->id }}"
                                                        value="{{ \App\Enums\AttendanceStatus::Presente->value }}"
                                                        @checked($selectedStatus === \App\Enums\AttendanceStatus::Presente->value)
                                                    >
                                                    <label class="form-check-label text-success" for="present-{{ $inscription->id }}">Présente</label>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-check-inline m-0">
                                                    <input
                                                        class="form-check-input attendance-status"
                                                        type="radio"
                                                        name="presences[{{ $inscription->id }}][status]"
                                                        id="absent-{{ $inscription->id }}"
                                                        value="{{ \App\Enums\AttendanceStatus::Absente->value }}"
                                                        @checked($selectedStatus === \App\Enums\AttendanceStatus::Absente->value)
                                                    >
                                                    <label class="form-check-label text-danger" for="absent-{{ $inscription->id }}">Absente</label>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                @if($existingPresence)
                                                    <span class="badge badge-info">Déjà pointée</span>
                                                @else
                                                    <span class="badge badge-secondary">À enregistrer</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="card-action d-flex flex-column flex-sm-row gap-2 justify-content-between">
                            <a href="{{ route('presences.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Voir les présences</a>
                            <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i> Enregistrer le pointage</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectStatuses = function (status) {
        document.querySelectorAll('.attendance-status[value="' + status + '"]').forEach(function (input) {
            input.checked = true;
        });
    };

    document.getElementById('markAllPresent')?.addEventListener('click', function () {
        selectStatuses('presente');
    });

    document.getElementById('markAllAbsent')?.addEventListener('click', function () {
        selectStatuses('absente');
    });

    document.getElementById('participantSearch')?.addEventListener('input', function (event) {
        const term = event.target.value.toLocaleLowerCase('fr-FR').trim();

        document.querySelectorAll('.attendance-row').forEach(function (row) {
            row.classList.toggle('d-none', !row.dataset.participante.includes(term));
        });
    });
});
</script>
@endpush
