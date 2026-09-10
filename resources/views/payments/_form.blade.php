@php
    $selectedInscription = old('inscription_id', $paiement->inscription_id);
    $selectedType = old('type', $paiement->type?->value ?? 'paiement');
    $selectedMode = old('payment_mode', $paiement->payment_mode?->value ?? 'especes');
@endphp

@if ($errors->any())
    <div class="alert alert-danger"><strong>Erreur !</strong> Veuillez corriger les erreurs ci-dessous.</div>
@endif

<div class="form-group">
    <label for="inscription_id">Inscription <span class="text-danger">*</span></label>
    <select name="inscription_id" id="inscription_id" class="form-select @error('inscription_id') is-invalid @enderror" required>
        <option value="">-- Choisir une inscription --</option>
        @foreach ($inscriptions as $inscription)
            <option value="{{ $inscription->id }}" @selected((int) $selectedInscription === $inscription->id)>
                {{ $inscription->participante->full_name }} — {{ $inscription->challenge->challengeType->label }} — {{ $inscription->challenge->start_date->format('d/m/Y') }}
            </option>
        @endforeach
    </select>
    @error('inscription_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="row">
    <div class="col-md-4"><div class="form-group"><label for="amount">Montant <span class="text-danger">*</span></label><input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" required value="{{ old('amount', $paiement->amount) }}">@error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
    <div class="col-md-4"><div class="form-group"><label for="type">Type <span class="text-danger">*</span></label><select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>@foreach ($types as $type)<option value="{{ $type->value }}" @selected($selectedType === $type->value)>{{ $type->label() }}</option>@endforeach</select>@error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
    <div class="col-md-4"><div class="form-group"><label for="payment_date">Date <span class="text-danger">*</span></label><input type="date" name="payment_date" id="payment_date" class="form-control @error('payment_date') is-invalid @enderror" required value="{{ old('payment_date', optional($paiement->payment_date)->format('Y-m-d') ?? now()->toDateString()) }}">@error('payment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
</div>

<div class="form-group"><label for="payment_mode">Mode de paiement <span class="text-danger">*</span></label><select name="payment_mode" id="payment_mode" class="form-select @error('payment_mode') is-invalid @enderror" required>@foreach ($modes as $mode)<option value="{{ $mode->value }}" @selected($selectedMode === $mode->value)>{{ $mode->label() }}</option>@endforeach</select>@error('payment_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
<div class="form-group"><label for="comment">Commentaire</label><textarea name="comment" id="comment" class="form-control @error('comment') is-invalid @enderror" rows="3">{{ old('comment', $paiement->comment) }}</textarea>@error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
