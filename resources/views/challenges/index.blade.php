@extends('layouts.app')

@section('title', 'Sessions')

@section('contenus')
<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Sessions de challenge</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('accueil') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('challenges.index') }}">Sessions</a></li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h4 class="card-title">Liste des sessions</h4>
            @can('create', \App\Models\Challenge::class)
                <a href="{{ route('challenges.create') }}" class="btn btn-primary btn-round ms-auto"><i class="fa fa-plus"></i> Nouvelle session</a>
            @endcan
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('challenges.index') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label for="q" class="form-label">Recherche</label>
                    <input type="text" name="q" id="q" class="form-control" value="{{ request('q') }}" placeholder="Type de challenge">
                </div>
                <div class="col-md-4">
                    <label for="challenge_type_id" class="form-label">Type</label>
                    <select name="challenge_type_id" id="challenge_type_id" class="form-select">
                        <option value="">Tous les types</option>
                        @foreach ($challengeTypes as $challengeType)
                            <option value="{{ $challengeType->id }}" @selected((int) request('challenge_type_id') === $challengeType->id)>{{ $challengeType->label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filtrer</button>
                    <a href="{{ route('challenges.index') }}" class="btn btn-secondary">Réinitialiser</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr><th>Type</th><th>Début</th><th>Fin</th><th>Durée</th><th>Inscriptions</th><th class="text-center">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($challenges as $challenge)
                            <tr>
                                <td>{{ $challenge->challengeType->label }}</td>
                                <td>{{ $challenge->start_date->format('d/m/Y') }}</td>
                                <td>{{ $challenge->end_date->format('d/m/Y') }}</td>
                                <td>{{ $challenge->duration_days }} jours</td>
                                <td>{{ $challenge->inscriptions_count }}</td>
                                <td class="text-center">
                                    <a href="{{ route('challenges.show', $challenge) }}" class="btn btn-link btn-primary"><i class="fa fa-eye"></i></a>
                                    @can('update', $challenge)<a href="{{ route('challenges.edit', $challenge) }}" class="btn btn-link btn-warning"><i class="fa fa-edit"></i></a>@endcan
                                    @can('delete', $challenge)
                                        <form action="{{ route('challenges.destroy', $challenge) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-link btn-danger" title="Supprimer"><i class="fa fa-trash"></i></button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center"><div class="alert alert-info mb-0">Aucune session trouvée.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $challenges->links() }}</div>
        </div>
    </div>
</div>
@endsection
