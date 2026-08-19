@extends('layouts.app')

@section('title', 'Bienvenue')

@section('contenus')
<div class="page-inner">
    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
        <div>
            <h3 class="fw-bold mb-3">Gestion du centre fitness</h3>
            <h6 class="op-7 mb-2">Suivi des participantes, challenges, paiements, presences et mesures.</h6>
        </div>
        <div class="ms-md-auto py-2 py-md-0">
            <a href="{{ route('accueil') }}" class="btn btn-primary btn-round me-2">
                <i class="fas fa-home me-1"></i> Tableau de bord
            </a>
            @can('create-participantes')
                <a href="{{ route('participantes.create') }}" class="btn btn-label-info btn-round">
                    <i class="fas fa-user-plus me-1"></i> Inscription
                </a>
            @endcan
        </div>
    </div>

    <div class="row">
        @can('show-participantes')
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('participantes.index') }}" class="text-decoration-none">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-primary bubble-shadow-small">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Participantes</p>
                                        <h4 class="card-title">Fiches</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endcan

        @can('show-challenges')
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('challenges.index') }}" class="text-decoration-none">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-success bubble-shadow-small">
                                        <i class="fas fa-dumbbell"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Challenges</p>
                                        <h4 class="card-title">Suivi</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endcan

        @can('show-payments')
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('payments.index') }}" class="text-decoration-none">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-info bubble-shadow-small">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Paiements</p>
                                        <h4 class="card-title">Encaissement</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endcan

        @can('record-attendance')
            <div class="col-md-6 col-lg-3">
                <a href="{{ route('presences.index') }}" class="text-decoration-none">
                    <div class="card card-stats card-round">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-icon">
                                    <div class="icon-big text-center icon-warning bubble-shadow-small">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                </div>
                                <div class="col col-stats ms-3 ms-sm-0">
                                    <div class="numbers">
                                        <p class="card-category">Presences</p>
                                        <h4 class="card-title">Seances</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endcan
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-title">Flux de travail</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <span class="btn btn-icon btn-round btn-primary me-3"><i class="fas fa-user-plus"></i></span>
                                <div>
                                    <div class="fw-bold">Inscription</div>
                                    <div class="text-muted small">Creation de la fiche participante.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <span class="btn btn-icon btn-round btn-success me-3"><i class="fas fa-flag"></i></span>
                                <div>
                                    <div class="fw-bold">Challenge</div>
                                    <div class="text-muted small">Programme et objectif de suivi.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <span class="btn btn-icon btn-round btn-info me-3"><i class="fas fa-receipt"></i></span>
                                <div>
                                    <div class="fw-bold">Paiement</div>
                                    <div class="text-muted small">Encaissement et recu.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center">
                                <span class="btn btn-icon btn-round btn-warning me-3"><i class="fas fa-ruler"></i></span>
                                <div>
                                    <div class="fw-bold">Progression</div>
                                    <div class="text-muted small">Presences, mesures et medias.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
