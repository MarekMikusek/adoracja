<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class IntentionData
{
    public function __construct(
        public string $intention,
        public ?int $userId,
    ) {}
}