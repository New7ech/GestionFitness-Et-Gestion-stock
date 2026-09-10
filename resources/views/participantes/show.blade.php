@extends('layouts.app')

@section('title', 'Fiche participante')

@section('contenus')
<div class="page-inner">
    <div class="page-header"><h3 class="fw-bold mb-3">{{ $participante->full_name }}</h3></div>
    <div class="row">
        <div class="col-lg-4"><div class="card"><div class="card-body text-center">@if ($participante->photo_path)<img src="{{ route('participantes.photo', $participante) }}" class="rounded-circle mb-3" style="width: 130px; height: 130px; object-fit: cover;" alt="Photo de {{ $participante->full_name }}">@endif<h4>{{ $participante->full_name }}</h4><p class="text-muted mb-1">{{ $participante->phone }}</p><p class="text-muted">{{ $participante->email ?: '—' }}</p>@can('update', $participante)<a href="{{ route('participantes.edit', $participante) }}" class="btn btn-warning">Modifier</a>@endcan @can('create', \App\Models\Inscription::class)<a href="{{ route('inscriptions.create', ['participante_id' => $participante->id]) }}" class="btn btn-primary">Nouvelle inscription</a>@endcan</div></div></div>
        <div class="col-lg-8"><div class="card"><div class="card-header"><h4 class="card-title">Inscriptions et historique</h4></div><div class="card-body"><div class="table-responsive"><table class="table table-striped table-hover"><thead><tr><th>Session</th><th>Période</th><th>Statut</th><th>Prix</th><th>Données</th><th></th></tr></thead><tbody>@forelse ($participante->inscriptions as $inscription)<tr><td>{{ $inscription->challenge->challengeType->label }}</td><td>{{ $inscription->challenge->start_date->format('d/m/Y') }} — {{ $inscription->challenge->end_date->format('d/m/Y') }}</td><td>{{ $inscription->status->label() }}</td><td>{{ number_format((float) $inscription->price, 0, ',', ' ') }} FCFA</td><td>{{ $inscription->paiements->count() }} paiement(s), {{ $inscription->presences->count() }} présence(s), {{ $inscription->mesures->count() }} mesure(s)</td><td><a href="{{ route('inscriptions.show', $inscription) }}" class="btn btn-link btn-primary"><i class="fa fa-eye"></i></a></td></tr>@empty<tr><td colspan="6" class="text-center">Aucune inscription enregistrée.</td></tr>@endforelse</tbody></table></div></div></div></div>
    </div>
</div>
@endsection
