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
            'article_id' => 'required|integer|exists:articles,id',
            'name' => 'required|string',
            'created_by' => 'required|integer|exists:users,id',
            'status' => 'required|boolean',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'created_by' => Auth::guard('api')->user()->id,
            'status' => true,
            'article_id' => $this->input('object_id'),
        ]);
    }

    public function failedValidation(Validator $validator)
    {
        throw new GeneralJsonException($validator->errors(), 422);
    }
}
