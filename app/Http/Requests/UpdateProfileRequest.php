<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($this->user()->id)
            ],
            'phone_number' => ['nullable', 'string', 'max:255'],
            'notification_preference' => ['required', 'in:email,sms'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Pole imię jest wymagane.',
            'last_name.required' => 'Pole nazwisko jest wymagane.',
            'email.required' => 'Pole email jest wymagane.',
            'email.email' => 'Podany email jest nieprawidłowy.',
            'email.unique' => 'Ten email jest już zajęty.',
            'notification_preference.required' => 'Wybierz sposób powiadomień.',
            'notification_preference.in' => 'Nieprawidłowy sposób powiadomień.',
        ];
    }
} 