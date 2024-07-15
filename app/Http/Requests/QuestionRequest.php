<?php

namespace App\Http\Requests;

use App\Exceptions\GeneralJsonException;
use App\Models\Question;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class QuestionRequest extends FormRequest
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


    public function rules()
    {
        $rules = [
            'object_id' => 'required|integer|exists:articles,id',
            'questions' => 'required|array',
            'level' => 'required|integer|exists:levels,id',
            'questions.*.id' => 'required|integer|exists:questions,id',
            'questions.*.status' => 'required|boolean',
        ];

        // Dinamik qoidalarni qo'shish
        if ($this->has('dynamic_rules')) {
            $rules = array_merge($rules, $this->input('dynamic_rules'));
        }

        return $rules;
    }


    protected function prepareForValidation()
    {
        $rules = [];

        foreach ($this->questions as $index => $question) {
            if ($question['status'] === false) {
//                $rules["questions.$index.image"] = 'required|file|mimes:jpeg,png,jpg,gif,svg';
                $rules["questions.$index.images"] = 'required';
//                $rules["questions.$index.description"] = 'required|string';
            }
        }

        $this->merge(['dynamic_rules' => $rules]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = Auth::guard('api')->user();
            $userRoleId = $user->roles->first()->pivot->role_id;
            foreach ($this->questions as $index => $question) {
                $questionModel = Question::find($question['id']);
                if ($questionModel && $questionModel->author_id !== $userRoleId) {
                    $validator->errors()->add("questions.$index.id", 'The question does not belong to the role of the authenticated user.');
                }
            }
        });
    }

    public function messages()
    {
        return [
            'questions.*.image.required' => 'Image is required when status is false.',
            'questions.*.description.required' => 'Description is required when status is false.',
            'questions.*.id.exists' => 'The selected question does not exist.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new GeneralJsonException($validator->errors(), 422);
    }
}
