<?php

namespace App\Http\Requests;

use App\Services\Helper;
use Illuminate\Foundation\Http\FormRequest;

class PatternStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hour'=> 'required|integer|between:0,23',
            'day' => 'required|string',
            'duty_type' => 'required|string',
            'start_date' => 'nullable|string',
            'repeat_interval' => 'required|integer'
        ];
    }
}
