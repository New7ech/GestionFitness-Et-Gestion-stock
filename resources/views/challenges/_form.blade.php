@php
    $selectedType = old('challenge_type_id', $challenge->challenge_type_id);
    $selectedDuration = old('duration_days', $challenge->duration_days);
@endphp

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Erreur !</strong> Veuillez corriger les erreurs ci-dessous.
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="challenge_type_id">Type de challenge <span class="text-danger">*</span></label>
            <select name="challenge_type_id" id="challenge_type_id" class="form-select @error('challenge_type_id') is-invalid @enderror" required>
                <option value="">-- Choisir un type --</option>
                @foreach ($challengeTypes as $challengeType)
                    <option value="{{ $challengeType->id }}" @selected((int) $selectedType === $challengeType->id)>{{ $challengeType->label }}</option>
                @endforeach
            </select>
            @error('challenge_type_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="start_date">Date de début <span class="text-danger">*</span></label>
            <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" required value="{{ old('start_date', optional($challenge->start_date)->format('Y-m-d') ?? now()->toDateString()) }}">
            @error('start_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label for="duration_days">Durée <span class="text-danger">*</span></label>
            <select name="duration_days" id="duration_days" class="form-select @error('duration_days') is-invalid @enderror" required>
                @foreach ($durations as $duration)
                    <option value="{{ $duration }}" @selected((int) $selectedDuration === (int) $duration)>{{ $duration }} jours</option>
                @endforeach
            </select>
            @error('duration_days')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

@if ($challenge->exists)
    <div class="form-check mt-3">
        <input type="hidden" name="confirm_schedule_change" value="0">
        <input type="checkbox" name="confirm_schedule_change" id="confirm_schedule_change" value="1" class="form-check-input" @checked(old('confirm_schedule_change'))>
        <label for="confirm_schedule_change" class="form-check-label">Je confirme la modification du planning si des données de suivi existent.</label>
    </div>
@endif
