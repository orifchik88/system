<?php

namespace App\Http\Requests;

use App\Exceptions\GeneralJsonException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class DxaResponseRegisterRequest extends FormRequest
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
            'commit' => 'string|required',
            'long' => 'string|required',
            'lat' => 'required|required',
            'administrative_status_id' => 'integer|required',
            'task_id' => 'integer|required|exists:dxa_responses,task_id',
            'images.*' => 'image|mimes:jpg,jpeg,png',
//            'documents.*' => 'sometimes',
//            'images.*' => 'required|image|mimes:jpg,jpeg,png',
            'blocks.*' => 'required',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new GeneralJsonException($validator->errors(), 422);
    }
}
