<div class="container">
    <div class="page-inner">
        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row pt-2 pb-4">
            <div>
                <h3 class="fw-bold mb-3">Tableau de bord</h3>
                <h6 class="op-7 mb-2">Application de gestion du centre fitness</h6>
            </div>
            <div class="ms-md-auto py-2 py-md-0">
                @can('create-participantes')
                    <a href="{{ route('participantes.create') }}" class="btn btn-primary btn-round">
                        <i class="fas fa-user-plus me-1"></i> Nouvelle inscription
                    </a>
                @endcan
            </div>
        </div>
    </div>
</div>
