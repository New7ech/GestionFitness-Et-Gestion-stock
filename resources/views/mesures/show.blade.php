@extends('layouts.app')

@section('title', 'Detail mesure')

@section('contenus')
<div class="page-inner">
    <div class="page-header"><h3 class="fw-bold mb-3">Mesure de {{ $mesure->inscription->participante->full_name }}</h3></div>
    <div class="card">
        <div class="card-header d-flex align-items-center"><h4 class="card-title">{{ $mesure->measured_at->format('d/m/Y') }} — {{ $mesure->stage->label() }}</h4><div class="ms-auto">@can('update', $mesure)<a href="{{ route('mesures.edit', $mesure) }}" class="btn btn-warning btn-round">Corriger</a>@endcan <a href="{{ route('inscriptions.show', $mesure->inscription) }}" class="btn btn-secondary btn-round">Inscription</a></div></div>
        <div class="card-body">
            <dl class="row"><dt class="col-sm-3">Session</dt><dd class="col-sm-9">{{ $mesure->inscription->challenge->challengeType->label }}</dd><dt class="col-sm-3">Poids</dt><dd class="col-sm-9">{{ number_format((float) $mesure->weight, 2, ',', ' ') }} kg</dd><dt class="col-sm-3">Tour de taille</dt><dd class="col-sm-9">{{ $mesure->waist ? number_format((float) $mesure->waist, 2, ',', ' ') . ' cm' : '—' }}</dd><dt class="col-sm-3">Commentaire</dt><dd class="col-sm-9">{{ $mesure->comment ?: '—' }}</dd></dl>
            <h5>Mesures complémentaires</h5><ul>@forelse ($mesure->values as $value)<li>{{ $value->measurementType->label }} : {{ $value->value }} {{ $value->measurementType->unit }}</li>@empty<li>Aucune mesure complémentaire.</li>@endforelse</ul>
            @can('create', \App\Models\Media::class)<hr><h5>Ajouter un média à l'inscription</h5><form action="{{ route('inscriptions.media.store', $mesure->inscription) }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end">@csrf<div class="col-md-3"><label for="type">Type</label><select name="type" id="type" class="form-select">@foreach ($mediaTypes as $type)<option value="{{ $type->value }}">{{ $type->label() }}</option>@endforeach</select></div><div class="col-md-3"><label for="stage">Étape</label><select name="stage" id="stage" class="form-select">@foreach ($stages as $stage)<option value="{{ $stage->value }}" @selected($mesure->stage === $stage)>{{ $stage->label() }}</option>@endforeach</select></div><div class="col-md-4"><label for="media">Fichier</label><input type="file" name="media" id="media" class="form-control" required></div><div class="col-md-2"><button class="btn btn-primary">Ajouter</button></div></form>@endcan
            <hr><h5>Médias associés à l'inscription</h5>@include('participant_media._grid', ['mediaItems' => $mesure->inscription->media])
        </div>
    </div>
</div>
@endsection
