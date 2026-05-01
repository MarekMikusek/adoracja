<?php

declare(strict_types=1);

namespace App\Actions\Testimonies;

use App\Models\Testimony;

class CreateTestimonyAction
{
    /**
     * @param array{nickname: string, testimony: string} $data
     */
    public function execute(array $data): Testimony
    {
        return Testimony::create([
            'nickname'     => $data['nickname'],
            'testimony'    => $data['testimony'],
            'is_confirmed' => false,
        ]);
    }
}
