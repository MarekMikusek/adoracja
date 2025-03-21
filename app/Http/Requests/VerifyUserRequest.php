<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => 'required|string',
        ];
    }
}
