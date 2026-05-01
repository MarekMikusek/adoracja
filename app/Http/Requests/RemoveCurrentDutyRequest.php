<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RemoveCurrentDutyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'duty_id' => ['required', 'string']
        ];
    }
}
