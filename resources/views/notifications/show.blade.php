@extends('layouts.app')

@section('title', 'Detail de la notification')

@section('contenus')
<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Notifications</h3>
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
                <a href="{{ route('notifications.index') }}">Notifications</a>
            </li>
            <li class="separator">
                <i class="icon-arrow-right"></i>
            </li>
            <li class="nav-item">
                <a href="#">Detail</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Details de la notification</div>
                    <div class="card-category">
                        Recue {{ $notification ? $notification->created_at->diffForHumans() : 'N/A' }}
                    </div>
                </div>
                <div class="card-body">
                    @if ($notification)
                        <div class="alert alert-{{ $notification->read_at ? 'light' : 'warning' }} notification-detail-message" role="alert">
                            <i class="fas fa-bell me-2"></i>
                            <strong>{{ $notification->data['message'] ?? 'Message non disponible.' }}</strong>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="form-group form-group-default">
                                    <label>Date de reception</label>
                                    <p class="form-control-static">{{ $notification->created_at->format('d/m/Y a H:i:s') }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-group-default">
                                    <label>Statut</label>
                                    <p class="form-control-static">
                                        @if($notification->read_at)
                                            <span class="badge badge-success"><i class="fas fa-check-circle me-1"></i>Lue le {{ $notification->read_at->format('d/m/Y a H:i:s') }}</span>
                                        @else
                                            <span class="badge badge-warning"><i class="fas fa-eye-slash me-1"></i>Non lue</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        @if(isset($notification->data['link']) && !empty($notification->data['link']))
                            <div class="mt-3">
                                <a href="{{ url($notification->data['link']) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-link me-1"></i> Voir l'element associe
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-danger text-center">
                            <p class="mb-0">Notification non trouvee ou acces non autorise.</p>
                        </div>
                    @endif
                </div>
                <div class="card-action text-end">
                    @if ($notification && ! $notification->read_at)
                        <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-check me-1"></i> Marquer comme lue
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('notifications.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Retour aux notifications
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .notification-detail-message {
        font-size: 1.1rem;
    }

    .badge-success {
        color: white !important;
    }

    .badge-warning {
        color: #664d03 !important;
        background-color: #fff3cd !important;
    }
</style>
@endpush
