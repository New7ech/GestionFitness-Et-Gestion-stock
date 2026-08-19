@extends('layouts.app')

@section('title', 'Mes notifications')

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
        </ul>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Toutes mes notifications</h4>
                        @if(Auth::user()->unreadNotifications->isNotEmpty())
                            <form action="{{ route('notifications.markAllAsRead') }}" method="POST" class="ms-auto mark-all-read-form">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-round btn-sm">
                                    <i class="fas fa-check-double me-1"></i> Marquer toutes comme lues
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($notifications->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                            <p class="text-muted fs-lg">Vous n'avez aucune notification pour le moment.</p>
                        </div>
                    @else
                        <ul class="list-group list-group-flush notification-list">
                            @foreach($notifications as $notification)
                                <li class="list-group-item notification-item {{ ! $notification->read_at ? 'notification-unread' : 'notification-read' }}">
                                    <a href="{{ route('notifications.show', $notification->id) }}" class="stretched-link text-decoration-none text-dark"></a>
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div>
                                            <span class="notification-icon me-3 fs-xl">
                                                <i class="fas fa-bell text-primary"></i>
                                            </span>
                                            <span class="notification-message">{{ $notification->data['message'] ?? 'Message non disponible.' }}</span>
                                        </div>
                                        <small class="text-muted notification-time ms-3">{{ $notification->created_at->diffForHumans(null, true) }}</small>
                                    </div>
                                    <div class="mt-2 d-flex justify-content-end align-items-center">
                                        @if(! $notification->read_at)
                                            <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST" class="mark-one-read-form me-2">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm py-1 px-2" data-bs-toggle="tooltip" title="Marquer comme lue">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="badge badge-success"><i class="fas fa-check-circle me-1"></i>Lue</span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>

                        @if($notifications->hasPages())
                            <div class="mt-4 d-flex justify-content-center">
                                {{ $notifications->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .notification-list .list-group-item {
        padding: 1rem 1.25rem;
        transition: background-color 0.2s ease-in-out;
        position: relative;
    }

    .notification-list .list-group-item:hover {
        background-color: #f8f9fa;
    }

    .notification-unread {
        background-color: #fff3cd;
        border-left: 4px solid #ffc107;
    }

    .notification-icon {
        opacity: 0.8;
    }

    .notification-message {
        font-weight: 500;
    }

    .notification-time {
        white-space: nowrap;
    }

    .badge-success {
        color: white !important;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]')).map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    $('.mark-all-read-form').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: 'Confirmer',
            text: 'Voulez-vous vraiment marquer toutes les notifications comme lues ?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Oui, marquer toutes',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush
