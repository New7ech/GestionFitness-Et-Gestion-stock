@extends('layouts.app')

@section('title', 'Statistiques fitness')

@section('contenus')
<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Statistiques fitness</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home">
                <a href="{{ route('accueil') }}">
                    <i class="icon-home"></i>
                </a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="{{ route('statistiques.index') }}">Statistiques</a>
            </li>
        </ul>
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
                                <p class="card-category">Participantes</p>
                                <h4 class="card-title">{{ number_format($totalParticipantes, 0, ',', ' ') }}</h4>
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
                                <i class="fas fa-flag-checkered"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Completion</p>
                                <h4 class="card-title">{{ number_format($tauxCompletion, 1, ',', ' ') }}%</h4>
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
                                <i class="fas fa-calendar-check"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Presence 30 jours</p>
                                <h4 class="card-title">{{ number_format($tauxPresence30Jours, 1, ',', ' ') }}%</h4>
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
                                <i class="fas fa-wallet"></i>
                            </div>
                        </div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Revenu net 30 jours</p>
                                <h4 class="card-title">{{ number_format($revenuNet30Jours, 0, ',', ' ') }} FCFA</h4>
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
                    <div class="card-title">Evolution moyenne des mesures</div>
                    <div class="card-category">Poids et tour de taille moyens sur les 30 derniers jours.</div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 360px">
                        <canvas id="mesuresEvolutionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-title">Statut des challenges</div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 360px">
                        <canvas id="statusChallengesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-title">Taux de presence quotidien</div>
                    <div class="card-category">Part des participantes presentes par jour sur les 30 derniers jours.</div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 330px">
                        <canvas id="presenceEvolutionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-title">Paiement des challenges</div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 330px">
                        <canvas id="statusPaiementsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-title">Revenus par type de challenge</div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 320px">
                        <canvas id="revenusParTypeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-title">Completion par type de challenge</div>
                </div>
                <div class="card-body p-0">
                    @if($completionParType->isEmpty())
                        <div class="text-center py-5 text-muted">Aucun challenge a analyser.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Type</th>
                                        <th class="text-center">Termines</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-end">Taux</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($completionParType as $type)
                                        <tr>
                                            <td>{{ $type['label'] }}</td>
                                            <td class="text-center">{{ number_format($type['termines'], 0, ',', ' ') }}</td>
                                            <td class="text-center">{{ number_format($type['total'], 0, ',', ' ') }}</td>
                                            <td class="text-end fw-bold">{{ number_format($type['taux'], 1, ',', ' ') }}%</td>
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
        <div class="col-md-12">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-title">Dernieres mesures enregistrees</div>
                </div>
                <div class="card-body p-0">
                    @if($mesuresRecentes->isEmpty())
                        <div class="text-center py-5 text-muted">Aucune mesure enregistree pour le moment.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Participante</th>
                                        <th>Challenge</th>
                                        <th class="text-center">Etape</th>
                                        <th class="text-end">Poids</th>
                                        <th class="text-end">Taille</th>
                                        <th class="text-end">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mesuresRecentes as $mesure)
                                        <tr>
                                            <td>
                                                <a href="{{ route('mesures.show', $mesure) }}" class="fw-bold text-decoration-none">
                                                    {{ $mesure->challenge->participante->full_name }}
                                                </a>
                                            </td>
                                            <td>{{ $mesure->challenge->challengeType->label }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-info">{{ $mesure->stage->label() }}</span>
                                            </td>
                                            <td class="text-end">{{ number_format((float) $mesure->weight, 2, ',', ' ') }} kg</td>
                                            <td class="text-end">
                                                {{ $mesure->waist !== null ? number_format((float) $mesure->waist, 2, ',', ' ').' cm' : '-' }}
                                            </td>
                                            <td class="text-end">{{ $mesure->measured_at?->format('d/m/Y') }}</td>
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
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const palette = ['#177dff', '#31ce36', '#f3545d', '#ffad46', '#48abf7', '#6861ce', '#1572e8'];

    function emptyDataset(data) {
        return !data || data.length === 0 || data.every(value => value === 0 || value === null);
    }

    const mesures = @json($mesuresEvolution);
    const mesuresCanvas = document.getElementById('mesuresEvolutionChart');
    if (mesuresCanvas) {
        new Chart(mesuresCanvas, {
            type: 'line',
            data: {
                labels: mesures.labels,
                datasets: [
                    {
                        label: 'Poids moyen (kg)',
                        data: mesures.weight,
                        borderColor: '#177dff',
                        backgroundColor: 'rgba(23, 125, 255, 0.12)',
                        fill: true,
                        tension: 0.35
                    },
                    {
                        label: 'Tour de taille moyen (cm)',
                        data: mesures.waist,
                        borderColor: '#f3545d',
                        backgroundColor: 'rgba(243, 84, 93, 0.10)',
                        fill: true,
                        tension: 0.35
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: false }, x: { grid: { display: false } } },
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    const challengeLabels = @json($statusChallenges->pluck('label'));
    const challengeData = @json($statusChallenges->pluck('total'));
    const challengeStatusCanvas = document.getElementById('statusChallengesChart');
    if (challengeStatusCanvas && !emptyDataset(challengeData)) {
        new Chart(challengeStatusCanvas, {
            type: 'doughnut',
            data: {
                labels: challengeLabels,
                datasets: [{ data: challengeData, backgroundColor: palette, borderWidth: 2 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    const presence = @json($presenceEvolution);
    const presenceCanvas = document.getElementById('presenceEvolutionChart');
    if (presenceCanvas) {
        new Chart(presenceCanvas, {
            type: 'bar',
            data: {
                labels: presence.labels,
                datasets: [{
                    label: 'Taux de presence',
                    data: presence.data,
                    backgroundColor: '#31ce36'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { callback: value => value + '%' }
                    },
                    x: { grid: { display: false } }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: context => context.parsed.y + '%' } }
                }
            }
        });
    }

    const paymentLabels = @json($statusPaiements->pluck('label'));
    const paymentData = @json($statusPaiements->pluck('total'));
    const paymentCanvas = document.getElementById('statusPaiementsChart');
    if (paymentCanvas && !emptyDataset(paymentData)) {
        new Chart(paymentCanvas, {
            type: 'doughnut',
            data: {
                labels: paymentLabels,
                datasets: [{ data: paymentData, backgroundColor: palette, borderWidth: 2 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    const revenusLabels = @json($revenusParType->pluck('label'));
    const revenusData = @json($revenusParType->pluck('total'));
    const revenusCanvas = document.getElementById('revenusParTypeChart');
    if (revenusCanvas) {
        new Chart(revenusCanvas, {
            type: 'bar',
            data: {
                labels: revenusLabels,
                datasets: [{
                    label: 'Revenu net',
                    data: revenusData,
                    backgroundColor: '#177dff'
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
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: context => new Intl.NumberFormat('fr-FR').format(context.parsed.y) + ' FCFA'
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
