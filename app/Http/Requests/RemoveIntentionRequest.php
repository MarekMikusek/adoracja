<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RemoveIntentionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'intention' => 'required|string'
        ];
    }
}
