<?php

namespace App\Http\Requests;

use App\Exceptions\GeneralJsonException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class DxaResponseRejectRequest extends FormRequest
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
            'reject_comment' => 'string|required',
            'task_id' => 'integer|required',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new GeneralJsonException($validator->errors(), 422);
    }
}
