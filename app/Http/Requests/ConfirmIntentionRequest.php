<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmIntentionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'intention' => 'required|string'
        ];
    }
}
