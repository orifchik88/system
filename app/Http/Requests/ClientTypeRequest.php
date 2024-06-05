<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientTypeRequest extends FormRequest
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
            'name' => 'required|string',
            'user_type_id' => 'required|exists:user_types,id',
            'self_registered' => 'boolean',
            'self_recovered' => 'boolean',
            'project_id' => 'nullable|integer',
            'shq_id' => 'nullable|integer',
            'confirm_by' => 'string',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'self_registered' => true,
            'self_recovered' => true,
            'project_id' => null,
            'shq_id' => null,
            'confirm_by' => 'PHONE',
        ]);


    }
}
