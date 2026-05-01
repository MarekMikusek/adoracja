<?php

declare(strict_types=1);

namespace App\Actions\Intentions;

use App\Models\Intention;

class CreateIntentionAction
{
    public function execute(string $content, ?int $userId): Intention
    {
        return Intention::create([
            'intention' => $content,
            'user_id' => $userId,
        ]);
    }
}
