@extends('layouts.app')

@section('title', 'Aide et support')

@section('contenus')
<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Centre d'aide</h3>
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
                <a href="#">Aide</a>
            </li>
        </ul>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Questions frequentes</h4>
                    <div class="card-category">Guides rapides pour les operations du centre fitness.</div>
                </div>
                <div class="card-body">
                    <div class="accordion accordion-bordered" id="accordionFAQ">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    Comment inscrire une participante ?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body">
                                    <ol>
                                        <li>Ouvrez le menu Fitness puis Participantes.</li>
                                        <li>Cliquez sur Inscription.</li>
                                        <li>Renseignez l'identite, le telephone, les informations de sante autorisees et la date d'inscription.</li>
                                        <li>Enregistrez la fiche, puis creez son challenge depuis la fiche participante.</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Comment suivre un challenge ?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body">
                                    <ul>
                                        <li>Associez le challenge a une participante et a un type de programme.</li>
                                        <li>La date de fin est calculee automatiquement selon la duree choisie.</li>
                                        <li>Ajoutez les presences, mesures et medias depuis les pages dediees ou la fiche du challenge.</li>
                                        <li>Changez le statut uniquement depuis l'action prevue afin de conserver le suivi.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Comment enregistrer un paiement et generer un recu ?
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body">
                                    <ol>
                                        <li>Ouvrez Paiements puis ajoutez un paiement pour le challenge concerne.</li>
                                        <li>Selectionnez le type, la date, le mode de paiement et le montant.</li>
                                        <li>Le solde du challenge est recalcule automatiquement.</li>
                                        <li>Depuis le paiement, utilisez l'action de generation du recu si elle est disponible.</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    Comment corriger une mesure ?
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionFAQ">
                                <div class="accordion-body">
                                    Une mesure deja saisie n'est pas ecrasee. La correction cree une nouvelle entree horodatee afin de conserver l'historique complet de progression.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-round">
                <div class="card-header">
                    <div class="card-title">Support</div>
                </div>
                <div class="card-body">
                    <p class="text-muted">Pour un probleme d'acces, de droit utilisateur, de recu ou de suivi participante, contactez l'administrateur du centre.</p>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="fas fa-envelope text-primary me-2"></i> support@example.com</li>
                        <li class="mb-2"><i class="fas fa-phone text-primary me-2"></i> +22X XX XX XX XX</li>
                        <li><i class="fas fa-clock text-primary me-2"></i> Lundi - Vendredi, 08h00 - 18h00</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .accordion-button:not(.collapsed) {
        color: var(--kai-primary-color);
        background-color: rgba(23, 125, 255, 0.08);
    }

    .accordion-button:focus {
        box-shadow: none;
    }

    .accordion-item {
        border: 1px solid #eee;
    }

    .accordion-body ul,
    .accordion-body ol {
        padding-left: 1.5rem;
    }
</style>
@endpush
