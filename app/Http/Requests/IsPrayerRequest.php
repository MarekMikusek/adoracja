<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IsPrayerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'is_prayer' => 'required|string',
            'intention_id' => 'required|string'
        ];
    }
}
