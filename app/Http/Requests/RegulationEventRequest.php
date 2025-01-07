<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegulationEventRequest extends FormRequest
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
            'regulation_id' => 'required|exists:regulations,id',
            'comment' => 'required|string',
            'status' => 'required|exists:regulation_statuses,id',
            'files' => 'sometimes|array',
            'images' => 'sometimes|array',
        ];
    }
}
