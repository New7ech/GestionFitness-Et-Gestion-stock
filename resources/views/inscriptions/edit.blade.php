@extends('layouts.app')

@section('title', 'Modifier une inscription')

@section('contenus')
<div class="page-inner">
    <div class="page-header"><h3 class="fw-bold mb-3">Modifier une inscription</h3></div>
    <div class="card"><div class="card-body">
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @if ($associationLocked)<div class="alert alert-info">La participante et la session sont verrouillées car cette inscription possède des données de suivi.</div>@endif
        <form action="{{ route('inscriptions.update', $inscription) }}" method="POST">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3"><label for="participante_id">Participante</label><select name="participante_id" id="participante_id" class="form-select" @disabled($associationLocked)>@foreach ($participantes as $participante)<option value="{{ $participante->id }}" @selected((int) old('participante_id', $inscription->participante_id) === $participante->id)>{{ $participante->full_name }} — {{ $participante->phone }}</option>@endforeach</select>@if($associationLocked)<input type="hidden" name="participante_id" value="{{ $inscription->participante_id }}">@endif</div>
                <div class="col-md-6 mb-3"><label for="challenge_id">Session</label><select name="challenge_id" id="challenge_id" class="form-select" @disabled($associationLocked)>@foreach ($challenges as $challenge)<option value="{{ $challenge->id }}" @selected((int) old('challenge_id', $inscription->challenge_id) === $challenge->id)>{{ $challenge->challengeType->label }} — {{ $challenge->start_date->format('d/m/Y') }}</option>@endforeach</select>@if($associationLocked)<input type="hidden" name="challenge_id" value="{{ $inscription->challenge_id }}">@endif</div>
                <div class="col-md-4 mb-3"><label for="status">Statut</label><select name="status" id="status" class="form-select">@foreach ($inscriptionStatuses as $status)<option value="{{ $status->value }}" @selected(old('status', $inscription->status->value) === $status->value)>{{ $status->label() }}</option>@endforeach</select></div>
                <div class="col-md-4 mb-3"><label for="price">Prix</label><input type="number" step="0.01" min="0.01" name="price" id="price" class="form-control" value="{{ old('price', $inscription->price) }}"></div>
                <div class="col-md-2 mb-3"><label for="goal_weight">Poids objectif</label><input type="number" step="0.01" min="0.01" name="goal_weight" id="goal_weight" class="form-control" value="{{ old('goal_weight', $inscription->goal_weight) }}"></div>
                <div class="col-md-2 mb-3"><label for="goal_waist">Taille objectif</label><input type="number" step="0.01" min="0.01" name="goal_waist" id="goal_waist" class="form-control" value="{{ old('goal_waist', $inscription->goal_waist) }}"></div>
            </div>
            <div class="mb-3"><label for="goal_text">Objectif principal</label><textarea name="goal_text" id="goal_text" class="form-control" rows="2">{{ old('goal_text', $inscription->goal_text) }}</textarea></div>
            <div class="mb-3"><label for="goal_personal">Objectif personnel</label><textarea name="goal_personal" id="goal_personal" class="form-control" rows="2">{{ old('goal_personal', $inscription->goal_personal) }}</textarea></div>
            <div class="mb-3"><label for="observations">Observations</label><textarea name="observations" id="observations" class="form-control" rows="3">{{ old('observations', $inscription->observations) }}</textarea></div>
            <div class="text-end"><button class="btn btn-primary">Mettre à jour</button><a href="{{ route('inscriptions.show', $inscription) }}" class="btn btn-secondary">Annuler</a></div>
        </form>
    </div></div>
</div>
@endsection
