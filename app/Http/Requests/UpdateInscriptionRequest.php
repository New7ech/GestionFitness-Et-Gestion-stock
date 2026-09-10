<?php

namespace App\Http\Requests;

use App\Enums\ChallengeStatus;
use App\Models\Inscription;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateInscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('edit-inscriptions') ?? false;
    }

    public function rules(): array
    {
        $inscription = $this->route('inscription');

        return [
            'participante_id' => ['required', 'exists:participantes,id'],
            'challenge_id' => [
                'required',
                'exists:challenges,id',
                Rule::unique('inscriptions', 'challenge_id')
                    ->ignore($inscription?->id)
                    ->where(fn (Builder $query): Builder => $query->where('participante_id', $this->integer('participante_id'))),
            ],
            'status' => ['required', Rule::enum(ChallengeStatus::class)],
            'goal_text' => ['nullable', 'string'],
            'goal_weight' => ['nullable', 'numeric', 'min:0.01'],
            'goal_waist' => ['nullable', 'numeric', 'min:0.01'],
            'goal_personal' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'participante_id.required' => 'La participante est obligatoire.',
            'participante_id.exists' => 'La participante sélectionnée est invalide.',
            'challenge_id.required' => 'La session est obligatoire.',
            'challenge_id.exists' => 'La session sélectionnée est invalide.',
            'challenge_id.unique' => 'Cette participante est déjà inscrite à cette session.',
            'status.required' => 'Le statut de l’inscription est obligatoire.',
            'price.required' => 'Le prix est obligatoire.',
            'price.numeric' => 'Le prix doit être un nombre.',
            'price.min' => 'Le prix doit être positif.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var Inscription|null $inscription */
            $inscription = $this->route('inscription');

            if (! $inscription || ! $this->associationChanged($inscription)) {
                return;
            }

            if ($inscription->paiements()->exists() || $inscription->presences()->exists() || $inscription->mesures()->exists()) {
                $validator->errors()->add('challenge_id', 'Une inscription avec des paiements, présences ou mesures ne peut pas être déplacée.');
            }
        });
    }

    private function associationChanged(Inscription $inscription): bool
    {
        return (int) $this->input('participante_id') !== (int) $inscription->participante_id
            || (int) $this->input('challenge_id') !== (int) $inscription->challenge_id;
    }
}
