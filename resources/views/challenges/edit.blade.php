@extends('layouts.app')

@section('title', 'Modifier une session')

@section('contenus')
<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Modifier une session</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('accueil') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('challenges.index') }}">Sessions</a></li>
        </ul>
    </div>

    <div class="card">
        <div class="card-header"><div class="card-title">{{ $challenge->challengeType?->label ?? 'Session' }}</div></div>
        <div class="card-body">
            <form action="{{ route('challenges.update', $challenge) }}" method="POST">
                @csrf
                @method('PUT')
                @include('challenges._form')
                <div class="card-action text-end">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Mettre à jour</button>
                    <a href="{{ route('challenges.show', $challenge) }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
