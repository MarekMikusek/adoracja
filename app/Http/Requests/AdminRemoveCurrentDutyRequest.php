<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminRemoveCurrentDutyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'users' => 'required|array',
            'current_duty_id' => 'required|string'
        ];
    }
}
