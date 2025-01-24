<?php

namespace App\Http\Requests;

use App\Exceptions\GeneralJsonException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class DxaResponseInspectorRequest extends FormRequest
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
            'task_id' => 'required|integer|exists:dxa_responses,task_id',
            'inspector_id' => 'required|integer|exists:users,id',
            'funding_source_id' => 'required|string|exists:funding_sources,id',
            'sphere_id' => 'required|integer',
            'program_id' => 'sometimes|integer',
            'end_term_work' => 'required_if:notification_type,1|string',
            'gnk_id' => 'sometimes|integer',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new GeneralJsonException($validator->errors(), 422);
    }
}
