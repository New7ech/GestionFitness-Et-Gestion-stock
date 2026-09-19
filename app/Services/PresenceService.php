<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Models\Challenge;
use App\Models\Presence;
use Illuminate\Support\Facades\DB;

class PresenceService
{
    /**
     * @param  array<int, array{status: string, comment?: string|null}>  $entries
     * @return array{created: int, updated: int, unchanged: int}
     */
    public function recordBulk(Challenge $challenge, string $attendanceDate, array $entries, int $userId): array
    {
        return DB::transaction(function () use ($challenge, $attendanceDate, $entries, $userId): array {
            $inscriptionIds = array_map('intval', array_keys($entries));
            $inscriptions = $challenge->inscriptions()
                ->whereKey($inscriptionIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $existingPresences = Presence::query()
                ->whereIn('inscription_id', $inscriptions->keys())
                ->whereDate('attendance_date', $attendanceDate)
                ->lockForUpdate()
                ->get()
                ->keyBy('inscription_id');

            $created = 0;
            $updated = 0;
            $unchanged = 0;

            foreach ($entries as $inscriptionId => $entry) {
                $presence = $existingPresences->get((int) $inscriptionId);
                $status = AttendanceStatus::from($entry['status']);
                $comment = filled($entry['comment'] ?? null) ? $entry['comment'] : null;

                if (! $presence) {
                    Presence::query()->create([
                        'inscription_id' => $inscriptionId,
                        'attendance_date' => $attendanceDate,
                        'status' => $status,
                        'comment' => $comment,
                        'recorded_by' => $userId,
                        'updated_by' => $userId,
                    ]);
                    $created++;

                    continue;
                }

                if ($presence->status === $status && $presence->comment === $comment) {
                    $unchanged++;

                    continue;
                }

                $presence->update([
                    'status' => $status,
                    'comment' => $comment,
                    'updated_by' => $userId,
                ]);
                $updated++;
            }

            return compact('created', 'updated', 'unchanged');
        });
    }
}
