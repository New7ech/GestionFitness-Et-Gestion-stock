<?php

namespace App\Http\Requests;

use App\Enums\ChallengeStatus;
use App\Enums\ParticipantStatus;
use App\Models\Challenge;
use App\Services\InscriptionService;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class StoreInscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create-inscriptions') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $inscription = $this->input('inscription', []);

        if (! is_array($inscription) || (($inscription['price'] ?? null) !== null && ($inscription['price'] ?? null) !== '')) {
            return;
        }

        $challengeTypeId = $this->input('challenge.challenge_type_id');

        if ($this->input('challenge_mode') === 'existing') {
            $challengeTypeId = Challenge::query()
                ->whereKey($this->input('challenge_id'))
                ->value('challenge_type_id');
        }

        $defaultPrice = app(InscriptionService::class)->defaultPriceForChallengeType($challengeTypeId);

        if ($defaultPrice !== null) {
            $inscription['price'] = $defaultPrice;
            $this->merge(['inscription' => $inscription]);
        }
    }

    public function rules(): array
    {
        return [
            'participante_mode' => ['required', Rule::in(['existing', 'new'])],
            'participante_id' => ['nullable', 'required_if:participante_mode,existing', 'exists:participantes,id'],
            'participante' => ['nullable', 'array'],
            'participante.first_name' => ['required_if:participante_mode,new', 'nullable', 'string', 'max:255'],
            'participante.last_name' => ['required_if:participante_mode,new', 'nullable', 'string', 'max:255'],
            'participante.phone' => ['required_if:participante_mode,new', 'nullable', 'string', 'max:50'],
            'participante.email' => ['nullable', 'email', 'max:255', Rule::unique('participantes', 'email')],
            'participante.address' => ['nullable', 'string', 'max:255'],
            'participante.photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'participante.birthdate' => ['nullable', 'date', 'before:today'],
            'participante.status' => ['required_if:participante_mode,new', 'nullable', Rule::enum(ParticipantStatus::class)],
            'participante.has_cesarean' => ['nullable', 'boolean'],
            'participante.cesarean_comment' => ['nullable', 'string'],
            'participante.health_notes' => ['nullable', 'string'],
            'participante.registration_date' => ['required_if:participante_mode,new', 'nullable', 'date'],
            'participante.confirm_duplicate_phone' => ['nullable', 'boolean'],

            'challenge_mode' => ['required', Rule::in(['existing', 'new'])],
            'challenge_id' => [
                'nullable',
                'required_if:challenge_mode,existing',
                'exists:challenges,id',
                Rule::unique('inscriptions', 'challenge_id')->where(
                    fn (Builder $query): Builder => $query->where('participante_id', $this->integer('participante_id'))
                ),
            ],
            'challenge' => ['nullable', 'array'],
            'challenge.challenge_type_id' => ['required_if:challenge_mode,new', 'nullable', 'exists:challenge_types,id'],
            'challenge.start_date' => ['required_if:challenge_mode,new', 'nullable', 'date'],
            'challenge.duration_days' => ['required_if:challenge_mode,new', 'nullable', 'integer', Rule::in(config('fitness.durations', [15, 30]))],

            'inscription' => ['required', 'array'],
            'inscription.status' => ['required', Rule::enum(ChallengeStatus::class)],
            'inscription.goal_text' => ['nullable', 'string'],
            'inscription.goal_weight' => ['nullable', 'numeric', 'min:0.01'],
            'inscription.goal_waist' => ['nullable', 'numeric', 'min:0.01'],
            'inscription.goal_personal' => ['nullable', 'string'],
            'inscription.observations' => ['nullable', 'string'],
            'inscription.price' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'participante_mode.required' => 'Le choix de participante est obligatoire.',
            'participante_mode.in' => 'Le choix de participante est invalide.',
            'participante_id.required_if' => 'La participante est obligatoire.',
            'participante_id.exists' => 'La participante sélectionnée est invalide.',
            'participante.first_name.required_if' => 'Le prénom est obligatoire.',
            'participante.last_name.required_if' => 'Le nom est obligatoire.',
            'participante.phone.required_if' => 'Le téléphone est obligatoire.',
            'participante.email.email' => 'L’adresse e-mail doit être valide.',
            'participante.email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'participante.photo.image' => 'Le fichier doit être une image.',
            'participante.photo.mimes' => 'La photo doit être de type jpeg, png, jpg ou webp.',
            'participante.photo.max' => 'La photo ne doit pas dépasser 2 Mo.',
            'participante.birthdate.before' => 'La date de naissance doit être antérieure à aujourd’hui.',
            'participante.status.required_if' => 'Le statut de la participante est obligatoire.',
            'participante.registration_date.required_if' => 'La date d’inscription est obligatoire.',

            'challenge_mode.required' => 'Le choix de session est obligatoire.',
            'challenge_mode.in' => 'Le choix de session est invalide.',
            'challenge_id.required_if' => 'La session est obligatoire.',
            'challenge_id.exists' => 'La session sélectionnée est invalide.',
            'challenge_id.unique' => 'Cette participante est déjà inscrite à cette session.',
            'challenge.challenge_type_id.required_if' => 'Le type de challenge est obligatoire.',
            'challenge.challenge_type_id.exists' => 'Le type de challenge sélectionné est invalide.',
            'challenge.start_date.required_if' => 'La date de début est obligatoire.',
            'challenge.duration_days.required_if' => 'La durée est obligatoire.',
            'challenge.duration_days.in' => 'La durée sélectionnée est invalide.',

            'inscription.status.required' => 'Le statut de l’inscription est obligatoire.',
            'inscription.price.required' => 'Le prix est obligatoire.',
            'inscription.price.numeric' => 'Le prix doit être un nombre.',
            'inscription.price.min' => 'Le prix doit être positif.',
            'inscription.goal_weight.numeric' => 'Le poids objectif doit être un nombre.',
            'inscription.goal_waist.numeric' => 'Le tour de taille objectif doit être un nombre.',
        ];
    }

    public function participanteData(): array
    {
        return Arr::only($this->input('participante', []), [
            'first_name',
            'last_name',
            'phone',
            'email',
            'address',
            'birthdate',
            'status',
            'has_cesarean',
            'cesarean_comment',
            'health_notes',
            'registration_date',
        ]);
    }

    public function challengeData(): array
    {
        return Arr::only($this->input('challenge', []), [
            'challenge_type_id',
            'start_date',
            'duration_days',
        ]);
    }

    public function inscriptionData(): array
    {
        return Arr::only($this->input('inscription', []), [
            'status',
            'goal_text',
            'goal_weight',
            'goal_waist',
            'goal_personal',
            'observations',
            'price',
        ]);
    }

    public function confirmDuplicatePhone(): bool
    {
        return $this->boolean('participante.confirm_duplicate_phone');
    }
}
