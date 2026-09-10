@php
    $selectedInscription = old('inscription_id', $presence->inscription_id);
    $selectedStatus = old('status', $presence->status?->value ?? 'presente');
@endphp

@if ($errors->any())<div class="alert alert-danger"><strong>Erreur !</strong> Veuillez corriger les erreurs ci-dessous.</div>@endif

<div class="row">
    <div class="col-md-6"><div class="form-group"><label for="inscription_id">Inscription <span class="text-danger">*</span></label>
        @if ($lockedInscription)
            <input type="hidden" name="inscription_id" value="{{ $presence->inscription_id }}">
            <input id="inscription_id" type="text" class="form-control" disabled value="{{ $presence->inscription->participante->full_name }} — {{ $presence->inscription->challenge->challengeType->label }}">
        @else
            <select name="inscription_id" id="inscription_id" class="form-select @error('inscription_id') is-invalid @enderror" required><option value="">-- Choisir une inscription --</option>@foreach ($inscriptions as $inscription)<option value="{{ $inscription->id }}" @selected((int) $selectedInscription === $inscription->id)>{{ $inscription->participante->full_name }} — {{ $inscription->challenge->challengeType->label }} — {{ $inscription->challenge->start_date->format('d/m/Y') }}</option>@endforeach</select>
        @endif
        @error('inscription_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div></div>
    <div class="col-md-3"><div class="form-group"><label for="attendance_date">Date <span class="text-danger">*</span></label><input type="date" name="attendance_date" id="attendance_date" class="form-control @error('attendance_date') is-invalid @enderror" required value="{{ old('attendance_date', optional($presence->attendance_date)->format('Y-m-d') ?? now()->toDateString()) }}">@error('attendance_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
    <div class="col-md-3"><div class="form-group"><label for="status">Statut <span class="text-danger">*</span></label><select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>{{ $status->label() }}</option>@endforeach</select>@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
</div>
<div class="form-group"><label for="comment">Commentaire</label><textarea name="comment" id="comment" class="form-control @error('comment') is-invalid @enderror" rows="3">{{ old('comment', $presence->comment) }}</textarea>@error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
