@extends('layouts.app')

@section('title', 'Détail inscription')

@section('contenus')
<div class="page-inner">
    <div class="page-header"><h3 class="fw-bold mb-3">Inscription de {{ $inscription->participante->full_name }}</h3></div>
    <div class="card">
        <div class="card-header d-flex align-items-center"><h4 class="card-title">{{ $inscription->challenge->challengeType->label }} — du {{ $inscription->challenge->start_date->format('d/m/Y') }} au {{ $inscription->challenge->end_date->format('d/m/Y') }}</h4><div class="ms-auto">@can('update', $inscription)<a href="{{ route('inscriptions.edit', $inscription) }}" class="btn btn-warning btn-round">Modifier</a>@endcan <a href="{{ route('participantes.show', $inscription->participante) }}" class="btn btn-secondary btn-round">Participante</a></div></div>
        <div class="card-body">
            @php
                $paidAmount = $inscription->paiements
                    ->filter(fn ($paiement) => $paiement->type === \App\Enums\PaymentType::Paiement)
                    ->sum('amount');
                $refundedAmount = $inscription->paiements
                    ->filter(fn ($paiement) => $paiement->type === \App\Enums\PaymentType::Remboursement)
                    ->sum('amount');
                $remainingAmount = max(0, (float) $inscription->price - $paidAmount + $refundedAmount);
            @endphp
            <dl class="row"><dt class="col-sm-3">Participante</dt><dd class="col-sm-9">{{ $inscription->participante->full_name }} — {{ $inscription->participante->phone }}</dd><dt class="col-sm-3">Statut</dt><dd class="col-sm-9">{{ $inscription->status->label() }}</dd><dt class="col-sm-3">Prix</dt><dd class="col-sm-9">{{ number_format((float) $inscription->price, 0, ',', ' ') }} FCFA</dd><dt class="col-sm-3">Solde</dt><dd class="col-sm-9">{{ number_format($remainingAmount, 0, ',', ' ') }} FCFA</dd><dt class="col-sm-3">Statut de paiement</dt><dd class="col-sm-9">{{ $inscription->payment_status->label() }}</dd><dt class="col-sm-3">Objectif</dt><dd class="col-sm-9">{{ $inscription->goal_text ?: '—' }}</dd><dt class="col-sm-3">Observations</dt><dd class="col-sm-9">{{ $inscription->observations ?: '—' }}</dd></dl>
            @can('changeStatus', $inscription)<form action="{{ route('inscriptions.status', $inscription) }}" method="POST" class="row g-2 align-items-end mb-4">@csrf @method('PATCH')<div class="col-md-4"><label for="status">Changer le statut</label><select name="status" id="status" class="form-select">@foreach (\App\Enums\ChallengeStatus::cases() as $status)<option value="{{ $status->value }}" @selected($inscription->status === $status)>{{ $status->label() }}</option>@endforeach</select></div><div class="col-md-2"><button class="btn btn-primary">Mettre à jour</button></div></form>@endcan
            <div class="d-flex gap-2 mb-4">@can('create', \App\Models\Paiement::class)<a href="{{ route('payments.create', ['inscription_id' => $inscription->id]) }}" class="btn btn-primary">Ajouter un paiement</a>@endcan @can('create', \App\Models\Presence::class)<a href="{{ route('presences.create', ['inscription_id' => $inscription->id]) }}" class="btn btn-primary">Ajouter une présence</a>@endcan @can('create', \App\Models\Mesure::class)<a href="{{ route('mesures.create', ['inscription_id' => $inscription->id]) }}" class="btn btn-primary">Ajouter une mesure</a>@endcan</div>
            <div class="row"><div class="col-lg-4"><h5>Paiements</h5><ul class="list-group mb-4">@forelse ($inscription->paiements as $paiement)<li class="list-group-item d-flex justify-content-between"><a href="{{ route('payments.show', $paiement) }}">{{ $paiement->payment_date->format('d/m/Y') }}</a><span>{{ number_format((float) $paiement->amount, 0, ',', ' ') }} FCFA</span></li>@empty<li class="list-group-item">Aucun paiement.</li>@endforelse</ul></div><div class="col-lg-4"><h5>Présences</h5><ul class="list-group mb-4">@forelse ($inscription->presences as $presence)<li class="list-group-item"><a href="{{ route('presences.show', $presence) }}">{{ $presence->attendance_date->format('d/m/Y') }}</a> — {{ $presence->status->label() }}</li>@empty<li class="list-group-item">Aucune présence.</li>@endforelse</ul></div><div class="col-lg-4"><h5>Mesures</h5><ul class="list-group mb-4">@forelse ($inscription->mesures as $mesure)<li class="list-group-item"><a href="{{ route('mesures.show', $mesure) }}">{{ $mesure->measured_at->format('d/m/Y') }}</a> — {{ number_format((float) $mesure->weight, 2, ',', ' ') }} kg</li>@empty<li class="list-group-item">Aucune mesure.</li>@endforelse</ul></div></div>
            @can('create', \App\Models\Media::class)<hr><h5>Ajouter un média à l'inscription</h5><form action="{{ route('inscriptions.media.store', $inscription) }}" method="POST" enctype="multipart/form-data" class="row g-3 align-items-end mb-4">@csrf<div class="col-md-3"><label for="type">Type</label><select name="type" id="type" class="form-select">@foreach (\App\Enums\MediaType::cases() as $type)<option value="{{ $type->value }}">{{ $type->label() }}</option>@endforeach</select></div><div class="col-md-3"><label for="stage">Étape</label><select name="stage" id="stage" class="form-select">@foreach (\App\Enums\MeasurementStage::cases() as $stage)<option value="{{ $stage->value }}">{{ $stage->label() }}</option>@endforeach</select></div><div class="col-md-4"><label for="media">Fichier</label><input type="file" name="media" id="media" class="form-control" required></div><div class="col-md-2"><button class="btn btn-primary">Ajouter</button></div></form>@endcan
            <h5>Médias</h5>@include('participant_media._grid', ['mediaItems' => $inscription->media])
            @can('delete', $inscription)<hr><form action="{{ route('inscriptions.destroy', $inscription) }}" method="POST">@csrf @method('DELETE')<button class="btn btn-danger">Supprimer l'inscription</button></form>@endcan
        </div>
    </div>
</div>
@endsection
