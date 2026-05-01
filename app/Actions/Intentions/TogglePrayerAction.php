<?php

declare(strict_types=1);

namespace App\Actions\Intentions;

use App\Models\Intention;

class TogglePrayerAction
{
    public function execute(int $intentionId, int $userId, bool $shouldPray): void
    {
        $intention = Intention::findOrFail($intentionId);

        if ($shouldPray) {
            $intention->participants()->syncWithoutDetaching([$userId]);
        } else {
            $intention->participants()->detach($userId);
        }
    }
}
