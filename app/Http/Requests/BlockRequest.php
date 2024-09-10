<?php

namespace App\Http\Requests;

use App\Exceptions\GeneralJsonException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BlockRequest extends FormRequest
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
            'dxa_response_id' => 'required|integer|exists:dxa_responses,id',
            'name' => 'required|string',
            'floor' => 'nullable|string',
            'construction_area' => 'nullable|string',
            'count_apartments' => 'nullable|string',
            'height' => 'nullable|string',
            'length' => 'nullable|string',
            'block_mode_id' => 'required|integer|exists:block_modes,id',
            'block_type_id' => 'required|integer|exists:block_types,id',
            'created_by' => 'required|integer|exists:users,id',
            'status' => 'required|boolean',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'created_by' => Auth::guard('api')->user()->id,
            'status' => true,
        ]);
    }

    public function failedValidation(Validator $validator)
    {
        throw new GeneralJsonException($validator->errors(), 422);
    }
}
