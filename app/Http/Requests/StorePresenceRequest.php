<?php

namespace App\Http\Requests;

use App\Enums\AttendanceStatus;
use App\Models\Inscription;
use App\Models\Presence;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePresenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('record-attendance') ?? false;
    }

    public function rules(): array
    {
        return [
            'inscription_id' => ['required', 'exists:inscriptions,id'],
            'attendance_date' => ['required', 'date'],
            'status' => ['required', Rule::enum(AttendanceStatus::class)],
            'comment' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'inscription_id.required' => 'L’inscription est obligatoire.',
            'inscription_id.exists' => 'L’inscription sélectionnée est invalide.',
            'attendance_date.required' => 'La date de présence est obligatoire.',
            'attendance_date.date' => 'La date de présence doit être valide.',
            'status.required' => 'Le statut de présence est obligatoire.',
            'status' => 'Le statut de présence sélectionné est invalide.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $inscriptionId = $this->integer('inscription_id');
            $attendanceDate = $this->date('attendance_date')?->toDateString();

            if (! $inscriptionId || ! $attendanceDate) {
                return;
            }

            $inscription = Inscription::query()->with('challenge')->find($inscriptionId);

            if (! $inscription) {
                return;
            }

            if (Presence::query()->where('inscription_id', $inscription->id)->whereDate('attendance_date', $attendanceDate)->exists()) {
                $validator->errors()->add('attendance_date', 'Une présence existe déjà pour cette inscription à cette date.');
            }

            if ($attendanceDate < $inscription->challenge->start_date->toDateString() || $attendanceDate > $inscription->challenge->end_date->toDateString()) {
                $validator->errors()->add('attendance_date', 'La date de présence doit être comprise dans la période de la session.');
            }
        });
    }
}
