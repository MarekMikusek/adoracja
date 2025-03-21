<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminStoreCurrentDutyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => 'required|string',
            'current_duty_id' => 'required|string',
            'duty_type' => 'required|in:gotowość,adoracja',
        ];
    }
}
