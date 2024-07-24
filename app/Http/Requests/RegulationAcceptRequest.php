<?php

namespace App\Http\Requests;

use App\Models\Regulation;
use Illuminate\Foundation\Http\FormRequest;

class RegulationAcceptRequest extends FormRequest
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
            'deadline' => 'required|string|date_format:d.m.Y',
            'regulation_id' => 'required|integer|exists:regulations,id',
            'comment' => 'required|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $regulation = Regulation::find($this->regulation_id);
            if ($regulation && $regulation->deadline_asked) {
                $validator->errors()->add('regulation_id', 'Deadline has already been asked for this regulation.');
            }
        });
    }
}
