<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignAdminRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'duty_id' => "required|string",
            'admin_id' => "required|string"
        ];
    }
}
