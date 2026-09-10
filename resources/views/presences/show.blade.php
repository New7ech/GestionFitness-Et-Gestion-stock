@extends('layouts.app')

@section('title', 'Détail présence')

@section('contenus')
<div class="page-inner"><div class="page-header"><h3 class="fw-bold mb-3">Présence de {{ $presence->inscription->participante->full_name }}</h3></div><div class="card"><div class="card-header d-flex align-items-center"><h4 class="card-title">{{ $presence->attendance_date->format('d/m/Y') }}</h4><div class="ms-auto">@can('update', $presence)<a href="{{ route('presences.edit', $presence) }}" class="btn btn-warning btn-round">Modifier</a>@endcan <a href="{{ route('inscriptions.show', $presence->inscription) }}" class="btn btn-secondary btn-round">Inscription</a></div></div><div class="card-body"><dl class="row"><dt class="col-sm-3">Session</dt><dd class="col-sm-9">{{ $presence->inscription->challenge->challengeType->label }}</dd><dt class="col-sm-3">Statut</dt><dd class="col-sm-9">{{ $presence->status->label() }}</dd><dt class="col-sm-3">Enregistrée par</dt><dd class="col-sm-9">{{ $presence->recordedBy?->name ?? '—' }}</dd><dt class="col-sm-3">Modifiée par</dt><dd class="col-sm-9">{{ $presence->updatedBy?->name ?? '—' }}</dd><dt class="col-sm-3">Commentaire</dt><dd class="col-sm-9">{{ $presence->comment ?: '—' }}</dd></dl></div></div></div>
@endsection
