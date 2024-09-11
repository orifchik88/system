<?php

namespace App\Http\Requests;

use App\Exceptions\GeneralJsonException;
use App\Models\Article;
use App\Models\DxaResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ObjectRequest extends FormRequest
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
            'response_id' => 'required|integer|exists:dxa_responses,id',
        ];
    }

//    public function withValidator(Validator $validator)
//    {
//        $validator->after(function ($validator) {
//            $responseId = $this->input('response_id');
//            $response = DxaResponse::find($responseId);
//
//            if ($response && Article::where('task_id', $response->task_id)->exists()) {
//                $validator->errors()->add('response_id', 'The selected response_id has a task_id that exists in the articles table.');
//            }
//        });
//    }



    public function failedValidation(Validator $validator)
    {
        throw new GeneralJsonException($validator->errors(), 422);
    }


}
