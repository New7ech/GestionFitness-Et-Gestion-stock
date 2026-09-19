@extends('layouts.app')

@section('title', 'Session')

@section('contenus')
<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Session {{ $challenge->challengeType->label }}</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('accueil') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('challenges.index') }}">Sessions</a></li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center">
            <div>
                <h4 class="card-title mb-1">{{ $challenge->challengeType->label }}</h4>
                <div class="text-muted">Du {{ $challenge->start_date->format('d/m/Y') }} au {{ $challenge->end_date->format('d/m/Y') }}</div>
            </div>
            <div class="ms-auto">
                @can('create', \App\Models\Inscription::class)
                    <a href="{{ route('inscriptions.create', ['challenge_id' => $challenge->id]) }}" class="btn btn-primary btn-round"><i class="fa fa-user-plus"></i> Inscrire une participante</a>
                @endcan
                @can('create', \App\Models\Presence::class)
                    @if($challenge->inscriptions->isNotEmpty())
                        <a href="{{ route('presences.bulk.create', ['challenge_id' => $challenge->id]) }}" class="btn btn-label-info btn-round"><i class="fa fa-calendar-check"></i> Pointer les présences</a>
                    @endif
                @endcan
                @can('update', $challenge)<a href="{{ route('challenges.edit', $challenge) }}" class="btn btn-warning btn-round">Modifier</a>@endcan
            </div>
        </div>
        <div class="card-body">
            @if ($hasHistoricalData)
                <div class="alert alert-info">Le planning est associé à des données de suivi ; sa modification demande une confirmation.</div>
            @endif
            <dl class="row mb-0">
                <dt class="col-sm-3">Durée</dt><dd class="col-sm-9">{{ $challenge->duration_days }} jours</dd>
                <dt class="col-sm-3">Créée par</dt><dd class="col-sm-9">{{ $challenge->createdBy?->name ?? 'N/A' }}</dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h4 class="card-title">Participantes inscrites ({{ $challenge->inscriptions->count() }})</h4></div>
        <div class="card-body p-0">
            @forelse ($challenge->inscriptions as $inscription)
                @if ($loop->first)<div class="table-responsive"><table class="table table-striped mb-0"><thead><tr><th>Participante</th><th>Statut</th><th>Prix</th><th>Solde</th><th>Paiement</th><th>Suivi</th><th></th></tr></thead><tbody>@endif
                    <tr>
                        @php
                            $paidAmount = $inscription->paiements
                                ->filter(fn ($paiement) => $paiement->type === \App\Enums\PaymentType::Paiement)
                                ->sum('amount');
                            $refundedAmount = $inscription->paiements
                                ->filter(fn ($paiement) => $paiement->type === \App\Enums\PaymentType::Remboursement)
                                ->sum('amount');
                            $remainingAmount = max(0, (float) $inscription->price - $paidAmount + $refundedAmount);
                        @endphp
                        <td>{{ $inscription->participante->full_name }}</td>
                        <td>{{ $inscription->status->label() }}</td>
                        <td>{{ number_format((float) $inscription->price, 0, ',', ' ') }} FCFA</td>
                        <td>{{ number_format($remainingAmount, 0, ',', ' ') }} FCFA</td>
                        <td>{{ $inscription->payment_status->label() }}</td>
                        <td>{{ $inscription->paiements->count() }} paiement(s), {{ $inscription->presences->count() }} présence(s), {{ $inscription->mesures->count() }} mesure(s)</td>
                        <td><a href="{{ route('inscriptions.show', $inscription) }}" class="btn btn-link btn-primary"><i class="fa fa-eye"></i></a></td>
                    </tr>
                @if ($loop->last)</tbody></table></div>@endif
            @empty
                <div class="text-center py-5 text-muted">Aucune participante n’est encore inscrite à cette session.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
