<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminUserStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users'],
            'phone_number' => ['nullable', 'string', 'max:255'],
            'notification_preference' => ['required', 'in:email,sms'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}