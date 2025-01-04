<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegulationFineRequest extends FormRequest
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
            'user_type' => 'required',
            'organization_name' => 'sometimes|string',
            'inn' => 'sometimes|integer',
            'decision_number' => 'required|string',
            'full_name' => 'required|string',
            'pinfl' => 'required|string',
            'position' => 'required|string',
            'decision_series' => 'required|string',
            'substance' => 'required|string',
            'substance_item' => 'required|string',
            'amount' => 'required|string',
            'date' => 'required|date',
            'files.*' => 'sometimes|array',
            'images.*' => 'sometimes|array',
        ];
    }
}
