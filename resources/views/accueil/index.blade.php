@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('contenus')
<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Tableau de bord fitness</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="{{ route('accueil') }}">
                    <i class="icon-home"></i>
                </a>
            </li>
        </ul>
    </div>

    <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row mb-4">
        <div>
            <h6 class="op-7 mb-1">Suivi des inscriptions, challenges, paiements et presences.</h6>
        </div>
        <div class="ms-md-auto py-2 py-md-0">
            @can('create-participantes')
                <a href="{{ route('participantes.create') }}" class="btn btn-primary btn-round me-2">
                    <i class="fas fa-user-plus me-1"></i> Inscription
                </a>
            @endcan
            @can('record-attendance')
                <a href="{{ route('presences.create') }}" class="btn btn-label-info btn-round me-2">
                    <i class="fas fa-calendar-check me-1"></i> Presence
                </a>
            @endcan
            @can('create-payments')
                <a href="{{ route('payments.create') }}" class="btn btn-label-success btn-round">
                    <i class="fas fa-money-bill-wave me-1"></i> Paiement
                </a>
            @endcan
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6 col-md-3">
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
                                <p class="card-category">Participantes actives</p>
                                <h4 class="card-title">{{ number_format($participantesActives, 0, ',', ' ') }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
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
                                <p class="card-category">Inscriptions en cours</p>
                                <h4 class="card-title">{{ number_format($challengesEnCours, 0, ',', ' ') }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-info bubble-shadow-small">
                                <i class="fas fa-cash-register"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">CA du mois</p>
                                <h4 class="card-title">{{ number_format($chiffreAffairesMois, 0, ',', ' ') }} FCFA</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon">
                            <div class="icon-big text-center icon-warning bubble-shadow-small">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Presences du jour</p>
                                <h4 class="card-title">{{ number_format($presencesDuJour, 0, ',', ' ') }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-title">Revenus des 7 derniers jours</div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 330px">
                        <canvas id="revenusJournalierChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-title">Challenges par type</div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 330px">
                        <canvas id="challengesParTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-title">Etat des inscriptions</div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Planifies</span>
                        <span class="badge badge-info">{{ number_format($challengesPlanifies, 0, ',', ' ') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">En cours</span>
                        <span class="badge badge-success">{{ number_format($challengesEnCours, 0, ',', ' ') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Termines</span>
                        <span class="badge badge-secondary">{{ number_format($challengesTermines, 0, ',', ' ') }}</span>
                    </div>
                </div>
            </div>

            <div class="card card-round">
                <div class="card-header">
                    <div class="card-title">Presences aujourd'hui</div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">Presentes</span>
                        <span class="badge badge-success">{{ number_format($presentesDuJour, 0, ',', ' ') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Absentes</span>
                        <span class="badge badge-danger">{{ number_format($absentesDuJour, 0, ',', ' ') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-title">Inscriptions a suivre</div>
                    <div class="card-category">Inscriptions en cours ou planifiees les plus recentes.</div>
                </div>
                <div class="card-body p-0">
                    @if($challengesRecents->isEmpty())
                        <div class="text-center py-5 text-muted">Aucune inscription a afficher.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Participante</th>
                                        <th>Type</th>
                                        <th class="text-center">Statut</th>
                                        <th class="text-end">Fin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($challengesRecents as $inscription)
                                        <tr>
                                            <td>
                                                <a href="{{ route('challenges.show', $inscription->challenge) }}" class="fw-bold text-decoration-none">
                                                    {{ $inscription->participante->full_name }}
                                                </a>
                                            </td>
                                            <td>{{ $inscription->challenge->challengeType->label }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-{{ $inscription->status->value === 'en_cours' ? 'success' : 'info' }}">
                                                    {{ $inscription->status->label() }}
                                                </span>
                                            </td>
                                            <td class="text-end">{{ $inscription->challenge->end_date?->format('d/m/Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-title">Paiements par mode ce mois</div>
                </div>
                <div class="card-body">
                    @if($paiementsParMode->isEmpty())
                        <div class="text-center py-4 text-muted">Aucun paiement enregistre ce mois.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mode</th>
                                        <th class="text-center">Operations</th>
                                        <th class="text-end">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paiementsParMode as $mode)
                                        <tr>
                                            <td>{{ $mode['label'] }}</td>
                                            <td class="text-center">{{ number_format($mode['count'], 0, ',', ' ') }}</td>
                                            <td class="text-end fw-bold">{{ number_format($mode['total'], 0, ',', ' ') }} FCFA</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-title">Recus recents</div>
                </div>
                <div class="card-body p-0">
                    @if($recusRecents->isEmpty())
                        <div class="text-center py-5 text-muted">Aucun recu emis pour le moment.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Numero</th>
                                        <th>Participante</th>
                                        <th class="text-end">Montant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recusRecents as $recu)
                                        <tr>
                                            <td>
                                                <a href="{{ route('recus.show', $recu) }}" class="fw-bold text-decoration-none">
                                                    {{ $recu->receipt_number }}
                                                </a>
                                            </td>
                                            <td>{{ $recu->participante_full_name }}</td>
                                            <td class="text-end">{{ number_format((float) $recu->amount_paid, 0, ',', ' ') }} FCFA</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($challengesACloturer->isNotEmpty())
        <div class="row">
            <div class="col-md-12">
                <div class="card card-round">
                    <div class="card-header">
                        <div class="card-title">Inscriptions a cloturer sous 7 jours</div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($challengesACloturer as $inscription)
                                <div class="col-md-6 col-lg-4">
                                    <div class="d-flex align-items-center border rounded p-3 mb-3">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-title rounded-circle bg-primary">
                                                {{ mb_substr($inscription->participante->first_name, 0, 1) }}{{ mb_substr($inscription->participante->last_name, 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="flex-1">
                                            <a href="{{ route('challenges.show', $inscription->challenge) }}" class="fw-bold text-decoration-none">
                                                {{ $inscription->participante->full_name }}
                                            </a>
                                            <div class="text-muted small">{{ $inscription->challenge->challengeType->label }} - fin {{ $inscription->challenge->end_date?->format('d/m/Y') }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const colors = ['#177dff', '#31ce36', '#f3545d', '#ffad46', '#48abf7', '#6861ce'];

    const revenusCanvas = document.getElementById('revenusJournalierChart');
    if (revenusCanvas) {
        new Chart(revenusCanvas, {
            type: 'line',
            data: {
                labels: @json($revenusJournalier['labels']),
                datasets: [{
                    label: 'Revenus nets',
                    data: @json($revenusJournalier['data']),
                    borderColor: '#177dff',
                    backgroundColor: 'rgba(23, 125, 255, 0.16)',
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => new Intl.NumberFormat('fr-FR').format(value) + ' FCFA'
                        }
                    },
                    x: { grid: { display: false } }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: context => new Intl.NumberFormat('fr-FR').format(context.parsed.y) + ' FCFA'
                        }
                    }
                }
            }
        });
    }

    const challengesCanvas = document.getElementById('challengesParTypeChart');
    const challengeLabels = @json($challengesParType->pluck('label'));
    const challengeData = @json($challengesParType->pluck('total'));
    if (challengesCanvas && challengeLabels.length > 0) {
        new Chart(challengesCanvas, {
            type: 'doughnut',
            data: {
                labels: challengeLabels,
                datasets: [{
                    data: challengeData,
                    backgroundColor: colors,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }
});
</script>
@endpush
