@extends('layouts.app')

@section('title', 'Inscriptions')

@section('contenus')
<div class="page-inner">
    <div class="page-header"><h3 class="fw-bold mb-3">Inscriptions</h3></div>
    <div class="card">
        <div class="card-header d-flex align-items-center"><h4 class="card-title">Liste des inscriptions</h4>@can('create', \App\Models\Inscription::class)<a href="{{ route('inscriptions.create') }}" class="btn btn-primary btn-round ms-auto"><i class="fa fa-plus"></i> Nouvelle inscription</a>@endcan</div>
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end mb-4">
                <div class="col-md-4"><label for="q">Recherche</label><input type="text" name="q" id="q" class="form-control" value="{{ request('q') }}" placeholder="Participante ou téléphone"></div>
                <div class="col-md-3"><label for="status">Statut</label><select name="status" id="status" class="form-select"><option value="">Tous</option>@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach</select></div>
                <div class="col-md-3"><label for="challenge_id">Session</label><select name="challenge_id" id="challenge_id" class="form-select"><option value="">Toutes</option>@foreach ($challenges as $challenge)<option value="{{ $challenge->id }}" @selected((int) request('challenge_id') === $challenge->id)>{{ $challenge->challengeType->label }} — {{ $challenge->start_date->format('d/m/Y') }}</option>@endforeach</select></div>
                <div class="col-md-2"><button class="btn btn-primary" type="submit">Filtrer</button> <a href="{{ route('inscriptions.index') }}" class="btn btn-secondary">Réinitialiser</a></div>
            </form>
            <div class="table-responsive"><table class="table table-striped table-hover"><thead><tr><th>Participante</th><th>Session</th><th>Statut</th><th>Prix</th><th>Paiement</th><th class="text-center">Actions</th></tr></thead><tbody>
                @forelse ($inscriptions as $inscription)
                    <tr><td>{{ $inscription->participante->full_name }}</td><td>{{ $inscription->challenge->challengeType->label }}<br><small>{{ $inscription->challenge->start_date->format('d/m/Y') }}</small></td><td>{{ $inscription->status->label() }}</td><td>{{ number_format((float) $inscription->price, 0, ',', ' ') }} FCFA</td><td>{{ $inscription->payment_status->label() }}</td><td class="text-center"><a href="{{ route('inscriptions.show', $inscription) }}" class="btn btn-link btn-primary"><i class="fa fa-eye"></i></a>@can('update', $inscription)<a href="{{ route('inscriptions.edit', $inscription) }}" class="btn btn-link btn-warning"><i class="fa fa-edit"></i></a>@endcan</td></tr>
                @empty
                    <tr><td colspan="6" class="text-center"><div class="alert alert-info mb-0">Aucune inscription trouvée.</div></td></tr>
                @endforelse
            </tbody></table></div>
            <div class="mt-3">{{ $inscriptions->links() }}</div>
        </div>
    </div>
</div>
@endsection
