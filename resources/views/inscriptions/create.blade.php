@extends('layouts.app')

@section('title', 'Nouvelle inscription')

@section('contenus')
<div class="page-inner">
    <div class="page-header">
        <h3 class="fw-bold mb-3">Nouvelle inscription</h3>
        <ul class="breadcrumbs mb-3">
            <li class="nav-home"><a href="{{ route('accueil') }}"><i class="icon-home"></i></a></li>
            <li class="separator"><i class="icon-arrow-right"></i></li>
            <li class="nav-item"><a href="{{ route('inscriptions.index') }}">Inscriptions</a></li>
        </ul>
    </div>

    <div class="card">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            <form action="{{ route('inscriptions.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @php($participantMode = old('participante_mode', 'existing'))
                @php($challengeMode = old('challenge_mode', request('challenge_id') ? 'existing' : 'existing'))
                @php($selectedChallenge = old('challenge_id', request('challenge_id')))

                <h5 class="fw-bold">Participante</h5>
                <div class="mb-3">
                    <div class="form-check form-check-inline"><input class="form-check-input participant-mode" type="radio" name="participante_mode" value="existing" id="participant-existing" @checked($participantMode === 'existing')><label class="form-check-label" for="participant-existing">Participante existante</label></div>
                    <div class="form-check form-check-inline"><input class="form-check-input participant-mode" type="radio" name="participante_mode" value="new" id="participant-new" @checked($participantMode === 'new')><label class="form-check-label" for="participant-new">Nouvelle participante</label></div>
                </div>
                <div id="existing-participant-fields" class="mb-3">
                    <label for="participante_id" class="form-label">Participante <span class="text-danger">*</span></label>
                    <select name="participante_id" id="participante_id" class="form-select @error('participante_id') is-invalid @enderror">
                        <option value="">-- Choisir une participante --</option>
                        @foreach ($participantes as $participante)
                            <option value="{{ $participante->id }}" @selected((int) old('participante_id') === $participante->id)>{{ $participante->full_name }} — {{ $participante->phone }}</option>
                        @endforeach
                    </select>
                    @error('participante_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div id="new-participant-fields" class="border rounded p-3 mb-4">
                    <div class="row">
                        <div class="col-md-4 mb-3"><label>Prénom</label><input type="text" name="participante[first_name]" class="form-control" value="{{ old('participante.first_name') }}"></div>
                        <div class="col-md-4 mb-3"><label>Nom</label><input type="text" name="participante[last_name]" class="form-control" value="{{ old('participante.last_name') }}"></div>
                        <div class="col-md-4 mb-3"><label>Téléphone</label><input type="text" name="participante[phone]" class="form-control" value="{{ old('participante.phone') }}"></div>
                        <div class="col-md-6 mb-3"><label>E-mail</label><input type="email" name="participante[email]" class="form-control" value="{{ old('participante.email') }}"></div>
                        <div class="col-md-3 mb-3"><label>Date de naissance</label><input type="date" name="participante[birthdate]" class="form-control" value="{{ old('participante.birthdate') }}"></div>
                        <div class="col-md-3 mb-3"><label>Statut</label><select name="participante[status]" class="form-select">@foreach ($participantStatuses as $status)<option value="{{ $status->value }}" @selected(old('participante.status', 'active') === $status->value)>{{ $status->label() }}</option>@endforeach</select></div>
                        <div class="col-md-6 mb-3"><label>Adresse</label><input type="text" name="participante[address]" class="form-control" value="{{ old('participante.address') }}"></div>
                        <div class="col-md-3 mb-3"><label>Date d’inscription</label><input type="date" name="participante[registration_date]" class="form-control" value="{{ old('participante.registration_date', now()->toDateString()) }}"></div>
                        <div class="col-md-3 mb-3"><label>Photo</label><input type="file" name="participante[photo]" class="form-control" accept=".jpeg,.jpg,.png,.webp"></div>
                    </div>
                    <div class="form-check"><input type="hidden" name="participante[has_cesarean]" value="0"><input class="form-check-input" type="checkbox" name="participante[has_cesarean]" value="1" id="has_cesarean" @checked(old('participante.has_cesarean'))><label class="form-check-label" for="has_cesarean">Césarienne déclarée</label></div>
                    <div class="mt-3"><label>Notes de santé</label><textarea name="participante[health_notes]" class="form-control" rows="2">{{ old('participante.health_notes') }}</textarea></div>
                    @if (session('warning') || old('participante.confirm_duplicate_phone'))
                        <div class="form-check mt-3"><input type="hidden" name="participante[confirm_duplicate_phone]" value="0"><input class="form-check-input" type="checkbox" name="participante[confirm_duplicate_phone]" value="1" id="confirm_duplicate_phone" @checked(old('participante.confirm_duplicate_phone'))><label class="form-check-label" for="confirm_duplicate_phone">Confirmer malgré le téléphone déjà utilisé</label></div>
                    @endif
                </div>

                <h5 class="fw-bold">Session</h5>
                <div class="mb-3">
                    <div class="form-check form-check-inline"><input class="form-check-input challenge-mode" type="radio" name="challenge_mode" value="existing" id="challenge-existing" @checked($challengeMode === 'existing')><label class="form-check-label" for="challenge-existing">Session existante</label></div>
                    <div class="form-check form-check-inline"><input class="form-check-input challenge-mode" type="radio" name="challenge_mode" value="new" id="challenge-new" @checked($challengeMode === 'new')><label class="form-check-label" for="challenge-new">Créer une nouvelle session</label></div>
                </div>
                <div id="existing-challenge-fields" class="mb-3">
                    <label for="challenge_id" class="form-label">Session <span class="text-danger">*</span></label>
                    <select name="challenge_id" id="challenge_id" class="form-select @error('challenge_id') is-invalid @enderror">
                        <option value="">-- Choisir une session --</option>
                        @foreach ($challenges as $challenge)
                            <option value="{{ $challenge->id }}" data-default-price="{{ $challenge->challengeType->default_price }}" @selected((int) $selectedChallenge === $challenge->id)>{{ $challenge->challengeType->label }} — {{ $challenge->start_date->format('d/m/Y') }} au {{ $challenge->end_date->format('d/m/Y') }}</option>
                        @endforeach
                    </select>
                    @error('challenge_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div id="new-challenge-fields" class="border rounded p-3 mb-4">
                    <div class="row">
                        <div class="col-md-4 mb-3"><label>Type <span class="text-danger">*</span></label><select name="challenge[challenge_type_id]" id="new_challenge_type_id" class="form-select"><option value="">-- Choisir --</option>@foreach ($challengeTypes as $challengeType)<option value="{{ $challengeType->id }}" data-default-price="{{ $challengeType->default_price }}" @selected((int) old('challenge.challenge_type_id') === $challengeType->id)>{{ $challengeType->label }}</option>@endforeach</select></div>
                        <div class="col-md-4 mb-3"><label>Date de début</label><input type="date" name="challenge[start_date]" class="form-control" value="{{ old('challenge.start_date', now()->toDateString()) }}"></div>
                        <div class="col-md-4 mb-3"><label>Durée</label><select name="challenge[duration_days]" class="form-select">@foreach ($durations as $duration)<option value="{{ $duration }}" @selected((int) old('challenge.duration_days', $durations[0]) === (int) $duration)>{{ $duration }} jours</option>@endforeach</select></div>
                    </div>
                </div>

                <h5 class="fw-bold">Données de l’inscription</h5>
                <div class="row">
                    <div class="col-md-4 mb-3"><label>Statut <span class="text-danger">*</span></label><select name="inscription[status]" class="form-select">@foreach ($inscriptionStatuses as $status)<option value="{{ $status->value }}" @selected(old('inscription.status', 'planifie') === $status->value)>{{ $status->label() }}</option>@endforeach</select></div>
                    <div class="col-md-4 mb-3"><label>Prix <span class="text-danger">*</span></label><input type="number" step="0.01" min="0.01" name="inscription[price]" id="inscription_price" class="form-control" value="{{ old('inscription.price') }}"></div>
                    <div class="col-md-2 mb-3"><label>Poids objectif</label><input type="number" step="0.01" min="0.01" name="inscription[goal_weight]" class="form-control" value="{{ old('inscription.goal_weight') }}"></div>
                    <div class="col-md-2 mb-3"><label>Taille objectif</label><input type="number" step="0.01" min="0.01" name="inscription[goal_waist]" class="form-control" value="{{ old('inscription.goal_waist') }}"></div>
                </div>
                <div class="mb-3"><label>Objectif principal</label><textarea name="inscription[goal_text]" class="form-control" rows="2">{{ old('inscription.goal_text') }}</textarea></div>
                <div class="mb-3"><label>Objectif personnel</label><textarea name="inscription[goal_personal]" class="form-control" rows="2">{{ old('inscription.goal_personal') }}</textarea></div>
                <div class="mb-3"><label>Observations</label><textarea name="inscription[observations]" class="form-control" rows="3">{{ old('inscription.observations') }}</textarea></div>
                <div class="text-end"><button class="btn btn-success" type="submit"><i class="fas fa-save"></i> Enregistrer</button><a href="{{ route('inscriptions.index') }}" class="btn btn-secondary">Annuler</a></div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    function toggleModes() {
        $('#existing-participant-fields').toggle($('#participant-existing').is(':checked'));
        $('#new-participant-fields').toggle($('#participant-new').is(':checked'));
        $('#existing-challenge-fields').toggle($('#challenge-existing').is(':checked'));
        $('#new-challenge-fields').toggle($('#challenge-new').is(':checked'));
    }
    $('.participant-mode, .challenge-mode').on('change', toggleModes);
    $('#challenge_id, #new_challenge_type_id').on('change', function () {
        const price = $(this).find(':selected').data('default-price');
        if (price && !$('#inscription_price').val()) $('#inscription_price').val(price);
    });
    toggleModes();
});
</script>
@endpush
