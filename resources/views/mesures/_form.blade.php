@php
    $selectedInscription = old('inscription_id', $mesure->inscription_id);
    $selectedStage = old('stage', $mesure->stage?->value ?? 'initiale');
@endphp

@if ($errors->any())<div class="alert alert-danger"><strong>Erreur !</strong> Veuillez corriger les erreurs ci-dessous.</div>@endif

<div class="row">
    <div class="col-md-6"><div class="form-group"><label for="inscription_id">Inscription <span class="text-danger">*</span></label>
        @if ($historizedUpdate)
            <input type="hidden" name="inscription_id" value="{{ $mesure->inscription_id }}">
            <input id="inscription_id" type="text" class="form-control" disabled value="{{ $mesure->inscription->participante->full_name }} — {{ $mesure->inscription->challenge->challengeType->label }}">
        @else
            <select name="inscription_id" id="inscription_id" class="form-select @error('inscription_id') is-invalid @enderror" required><option value="">-- Choisir une inscription --</option>@foreach ($inscriptions as $inscription)<option value="{{ $inscription->id }}" @selected((int) $selectedInscription === $inscription->id)>{{ $inscription->participante->full_name }} — {{ $inscription->challenge->challengeType->label }} — {{ $inscription->challenge->start_date->format('d/m/Y') }}</option>@endforeach</select>
        @endif
        @error('inscription_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div></div>
    <div class="col-md-3"><div class="form-group"><label for="measured_at">Date <span class="text-danger">*</span></label><input type="date" name="measured_at" id="measured_at" class="form-control @error('measured_at') is-invalid @enderror" required value="{{ old('measured_at', optional($mesure->measured_at)->format('Y-m-d') ?? now()->toDateString()) }}">@error('measured_at')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
    <div class="col-md-3"><div class="form-group"><label for="stage">Étape <span class="text-danger">*</span></label><select name="stage" id="stage" class="form-select @error('stage') is-invalid @enderror" required>@foreach ($stages as $stage)<option value="{{ $stage->value }}" @selected($selectedStage === $stage->value)>{{ $stage->label() }}</option>@endforeach</select>@error('stage')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
</div>

<div class="row"><div class="col-md-6"><div class="form-group"><label for="weight">Poids (kg) <span class="text-danger">*</span></label><input type="number" step="0.01" min="0.01" name="weight" id="weight" class="form-control @error('weight') is-invalid @enderror" required value="{{ old('weight', $mesure->weight) }}">@error('weight')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div><div class="col-md-6"><div class="form-group"><label for="waist">Tour de taille (cm)</label><input type="number" step="0.01" min="0.01" name="waist" id="waist" class="form-control @error('waist') is-invalid @enderror" value="{{ old('waist', $mesure->waist) }}">@error('waist')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div></div>

@if ($measurementTypes->isNotEmpty())
    <h5 class="mt-3">Mesures complémentaires</h5><div class="row">@foreach ($measurementTypes as $measurementType)<div class="col-md-4"><div class="form-group"><label for="measurement_values_{{ $measurementType->id }}">{{ $measurementType->label }} ({{ $measurementType->unit }})</label><input type="number" step="0.01" min="0.01" name="measurement_values[{{ $measurementType->id }}]" id="measurement_values_{{ $measurementType->id }}" class="form-control @error('measurement_values.'.$measurementType->id) is-invalid @enderror" value="{{ old('measurement_values.'.$measurementType->id, $mesure->values->firstWhere('measurement_type_id', $measurementType->id)?->value) }}">@error('measurement_values.'.$measurementType->id)<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>@endforeach</div>
@endif
<div class="form-group"><label for="comment">Commentaire</label><textarea name="comment" id="comment" rows="3" class="form-control @error('comment') is-invalid @enderror">{{ old('comment', $mesure->comment) }}</textarea>@error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
