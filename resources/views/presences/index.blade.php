@extends('layouts.app')

@section('title', 'Présences')

@section('contenus')
<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Présences</h3>
    </div>

    <div class="card card-round">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center gap-2">
            <h4 class="card-title">Feuille de présence</h4>
            @can('create', \App\Models\Presence::class)
                <div class="ms-md-auto d-flex gap-2">
                    <a href="{{ route('presences.create', request()->only('inscription_id')) }}" class="btn btn-outline-primary btn-round">
                        <i class="fa fa-plus me-1"></i> Une présence
                    </a>
                    <a href="{{ route('presences.bulk.create') }}" class="btn btn-primary btn-round">
                        <i class="fas fa-users me-1"></i> Pointer une session
                    </a>
                </div>
            @endcan
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end mb-4">
                <div class="col-md-3">
                    <label for="q" class="form-label">Recherche</label>
                    <input name="q" id="q" class="form-control" value="{{ request('q') }}">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Statut</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Tous</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Du</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Au</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary">Filtrer</button>
                    <a href="{{ route('presences.index') }}" class="btn btn-secondary">Réinitialiser</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Participante</th>
                            <th>Session</th>
                            <th>Statut</th>
                            <th>Enregistrée par</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($presences as $presence)
                            <tr>
                                <td>{{ $presence->attendance_date->format('d/m/Y') }}</td>
                                <td>{{ $presence->inscription->participante->full_name }}</td>
                                <td>{{ $presence->inscription->challenge->challengeType->label }}</td>
                                <td>{{ $presence->status->label() }}</td>
                                <td>{{ $presence->recordedBy?->name ?? '—' }}</td>
                                <td><a href="{{ route('presences.show', $presence) }}" class="btn btn-link btn-primary"><i class="fa fa-eye"></i></a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center">Aucune présence trouvée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $presences->links() }}
        </div>
    </div>
</div>
@endsection
