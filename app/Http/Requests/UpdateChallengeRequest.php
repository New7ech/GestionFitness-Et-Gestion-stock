<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChallengeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit-challenges') ?? false;
    }

    public function rules(): array
    {
        return [
            'challenge_type_id' => ['required', 'exists:challenge_types,id'],
            'start_date' => ['required', 'date'],
            'duration_days' => ['required', 'integer', Rule::in(config('fitness.durations', [15, 30]))],
            'confirm_schedule_change' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'challenge_type_id.required' => 'Le type de challenge est obligatoire.',
            'challenge_type_id.exists' => 'Le type de challenge sélectionné est invalide.',
            'start_date.required' => 'La date de début est obligatoire.',
            'start_date.date' => 'La date de début doit être valide.',
            'duration_days.required' => 'La durée est obligatoire.',
            'duration_days.integer' => 'La durée doit être un nombre entier.',
            'duration_days.in' => 'La durée sélectionnée est invalide.',
        ];
    }
}
