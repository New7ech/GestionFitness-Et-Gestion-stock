<?php

namespace App\Http\Requests;

use App\Enums\AttendanceStatus;
use App\Models\Challenge;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBulkPresenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('record-attendance') ?? false;
    }

    public function rules(): array
    {
        return [
            'challenge_id' => ['required', 'exists:challenges,id'],
            'attendance_date' => ['required', 'date'],
            'presences' => ['required', 'array', 'min:1'],
            'presences.*.status' => ['required', Rule::enum(AttendanceStatus::class)],
            'presences.*.comment' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'challenge_id.required' => 'La session est obligatoire.',
            'challenge_id.exists' => 'La session sélectionnée est invalide.',
            'attendance_date.required' => 'La date de présence est obligatoire.',
            'attendance_date.date' => 'La date de présence doit être valide.',
            'presences.required' => 'Sélectionnez au moins une participante.',
            'presences.array' => 'Les présences transmises sont invalides.',
            'presences.min' => 'Sélectionnez au moins une participante.',
            'presences.*.status.required' => 'Le statut de présence est obligatoire.',
            'presences.*.status' => 'Un statut de présence sélectionné est invalide.',
            'presences.*.comment.max' => 'Le commentaire ne peut pas dépasser 1 000 caractères.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $challenge = Challenge::query()->find($this->integer('challenge_id'));
            $attendanceDate = $this->date('attendance_date')?->toDateString();

            if (! $challenge || ! $attendanceDate) {
                return;
            }

            if ($attendanceDate < $challenge->start_date->toDateString() || $attendanceDate > $challenge->end_date->toDateString()) {
                $validator->errors()->add('attendance_date', 'La date de présence doit être comprise dans la période de la session.');
            }

            $submittedIds = collect(array_keys($this->input('presences', [])))
                ->map(fn (string|int $id): int => (int) $id)
                ->filter()
                ->values();

            $validIds = $challenge->inscriptions()
                ->whereKey($submittedIds)
                ->pluck('id')
                ->map(fn (int $id): int => $id);

            $submittedIds
                ->diff($validIds)
                ->each(function (int $inscriptionId) use ($validator): void {
                    $validator->errors()->add(
                        "presences.{$inscriptionId}",
                        'Cette participante n’est pas inscrite à la session sélectionnée.'
                    );
                });
        });
    }

    public function challenge(): Challenge
    {
        return Challenge::query()->findOrFail($this->integer('challenge_id'));
    }
}
