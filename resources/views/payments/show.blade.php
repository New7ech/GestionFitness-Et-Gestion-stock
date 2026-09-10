@extends('layouts.app')

@section('title', 'Detail paiement')

@section('contenus')
<div class="page-inner">
    <div class="page-header"><h3 class="fw-bold mb-3">Paiement</h3></div>
    <div class="card">
        <div class="card-header d-flex align-items-center"><h4 class="card-title">{{ $paiement->inscription->participante->full_name }}</h4><div class="ms-auto">@can('update', $paiement)<a class="btn btn-warning btn-round" href="{{ route('payments.edit', $paiement) }}">Modifier</a>@endcan <a class="btn btn-secondary btn-round" href="{{ route('inscriptions.show', $paiement->inscription) }}">Inscription</a></div></div>
        <div class="card-body"><dl class="row"><dt class="col-sm-3">Session</dt><dd class="col-sm-9">{{ $paiement->inscription->challenge->challengeType->label }}</dd><dt class="col-sm-3">Date</dt><dd class="col-sm-9">{{ $paiement->payment_date->format('d/m/Y') }}</dd><dt class="col-sm-3">Type</dt><dd class="col-sm-9">{{ $paiement->type->label() }}</dd><dt class="col-sm-3">Montant</dt><dd class="col-sm-9">{{ number_format((float) $paiement->amount, 0, ',', ' ') }} FCFA</dd><dt class="col-sm-3">Mode</dt><dd class="col-sm-9">{{ $paiement->payment_mode->label() }}</dd><dt class="col-sm-3">Solde restant</dt><dd class="col-sm-9">{{ number_format($remainingAmount, 0, ',', ' ') }} FCFA</dd><dt class="col-sm-3">Commentaire</dt><dd class="col-sm-9">{{ $paiement->comment ?: '-' }}</dd></dl>@if ($paiement->recu)<a href="{{ route('recus.show', $paiement->recu) }}" class="btn btn-primary">Voir le reçu</a>@else @can('generate-recus')<form action="{{ route('payments.recu.store', $paiement) }}" method="POST">@csrf<button class="btn btn-primary">Générer le reçu</button></form>@endcan @endif</div>
    </div>
</div>
@endsection
