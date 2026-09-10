@extends('layouts.app')

@section('title', 'Paiements')

@section('contenus')
<div class="page-inner">
    <div class="page-header"><h3 class="fw-bold mb-3">Paiements</h3></div>
    <div class="card"><div class="card-header d-flex align-items-center"><h4 class="card-title">Liste des paiements</h4>@can('create', \App\Models\Paiement::class)<a href="{{ route('payments.create') }}" class="btn btn-primary btn-round ms-auto"><i class="fa fa-plus"></i> Nouveau paiement</a>@endcan</div>
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end mb-4"><div class="col-md-6"><label for="q" class="form-label">Recherche</label><input type="text" name="q" id="q" class="form-control" value="{{ request('q') }}" placeholder="Participante ou téléphone"></div><div class="col-md-4"><button class="btn btn-primary"><i class="fa fa-search"></i> Filtrer</button> <a href="{{ route('payments.index') }}" class="btn btn-secondary">Réinitialiser</a></div></form>
            <div class="table-responsive"><table class="table table-striped table-hover"><thead><tr><th>Date</th><th>Participante</th><th>Session</th><th>Type</th><th>Montant</th><th>Reçu</th><th class="text-center">Actions</th></tr></thead><tbody>
                @forelse ($paiements as $paiement)<tr><td>{{ $paiement->payment_date->format('d/m/Y') }}</td><td>{{ $paiement->inscription->participante->full_name }}</td><td>{{ $paiement->inscription->challenge->challengeType->label }}</td><td>{{ $paiement->type->label() }}</td><td>{{ number_format((float) $paiement->amount, 0, ',', ' ') }} FCFA</td><td>{{ $paiement->recu?->receipt_number ?? '—' }}</td><td class="text-center"><a href="{{ route('payments.show', $paiement) }}" class="btn btn-link btn-primary"><i class="fa fa-eye"></i></a>@can('update', $paiement)<a href="{{ route('payments.edit', $paiement) }}" class="btn btn-link btn-warning"><i class="fa fa-edit"></i></a>@endcan</td></tr>
                @empty<tr><td colspan="7" class="text-center">Aucun paiement trouvé.</td></tr>@endforelse
            </tbody></table></div>{{ $paiements->links() }}
        </div>
    </div>
</div>
@endsection
