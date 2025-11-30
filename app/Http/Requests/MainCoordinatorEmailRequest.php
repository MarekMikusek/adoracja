<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MainCoordinatorEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Allow all authenticated users (or adjust if needed)
        return true;
    }

    public function rules(): array
    {
        return [
            'coordinator_id' => [
                'required',
                'integer',
                'exists:main_coordinators,id',
            ],
            'message' => [
                'required',
                'string',
                'min:1',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'coordinator_id.required' => 'Brak identyfikatora koordynatora.',
            'coordinator_id.integer'  => 'Identyfikator musi być liczbą całkowitą.',
            'coordinator_id.exists'   => 'Nie znaleziono takiego koordynatora.',
            'message.required'        => 'Treść wiadomości nie może być pusta.',
            'message.string'          => 'Nieprawidłowy format wiadomości.',
        ];
    }
}
