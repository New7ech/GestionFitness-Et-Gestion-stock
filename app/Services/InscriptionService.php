<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Exceptions\DuplicateParticipantePhoneException;
use App\Models\Challenge;
use App\Models\ChallengeType;
use App\Models\Inscription;
use App\Models\Participante;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class InscriptionService
{
    public function createChallenge(array $validated, int $userId): Challenge
    {
        return Challenge::query()->create(Arr::only($validated, [
            'challenge_type_id',
            'start_date',
            'duration_days',
        ]) + [
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    public function defaultPriceForChallengeType(mixed $challengeTypeId): ?string
    {
        if (! is_numeric($challengeTypeId)) {
            return null;
        }

        return ChallengeType::query()
            ->whereKey($challengeTypeId)
            ->value('default_price');
    }

    public function inscrire(
        string $participanteMode,
        ?int $participanteId,
        array $participanteData,
        string $challengeMode,
        ?int $challengeId,
        array $challengeData,
        array $inscriptionData,
        int $userId,
        ?UploadedFile $photo = null,
        bool $confirmDuplicatePhone = false
    ): Inscription {
        if ($participanteMode === 'new') {
            $duplicate = $this->duplicatePhone($participanteData['phone'] ?? '');

            if ($duplicate && ! $confirmDuplicatePhone) {
                throw new DuplicateParticipantePhoneException($duplicate);
            }
        }

        $storedPhotoPath = null;

        try {
            return DB::transaction(function () use (
                $participanteMode,
                $participanteId,
                $participanteData,
                $challengeMode,
                $challengeId,
                $challengeData,
                $inscriptionData,
                $userId,
                $photo,
                &$storedPhotoPath
            ): Inscription {
                $participante = $participanteMode === 'existing'
                    ? Participante::query()->lockForUpdate()->findOrFail($participanteId)
                    : Participante::query()->create($participanteData + [
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ]);

                if ($photo) {
                    $storedPhotoPath = $this->storePhoto($photo, $participante);
                    $participante->update(['photo_path' => $storedPhotoPath]);
                }

                $challenge = $challengeMode === 'existing'
                    ? Challenge::query()->lockForUpdate()->findOrFail($challengeId)
                    : $this->createChallenge($challengeData, $userId);

                return Inscription::query()->create($inscriptionData + [
                    'participante_id' => $participante->id,
                    'challenge_id' => $challenge->id,
                    'payment_status' => PaymentStatus::Impaye,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ])->load(['participante', 'challenge.challengeType']);
            });
        } catch (Throwable $exception) {
            if ($storedPhotoPath) {
                Storage::disk('participant_media')->delete($storedPhotoPath);
            }

            throw $exception;
        }
    }

    private function duplicatePhone(string $phone): ?Participante
    {
        return Participante::query()
            ->where('phone', $phone)
            ->first();
    }

    private function storePhoto(UploadedFile $photo, Participante $participante): string
    {
        $extension = strtolower($photo->getClientOriginalExtension());

        return $photo->storeAs(
            "participantes/{$participante->id}/profile",
            Str::random(40).'.'.$extension,
            'participant_media'
        );
    }
}
