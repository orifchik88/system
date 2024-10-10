<?php

namespace App\Http\Requests\ClaimRequests;

use Illuminate\Foundation\Http\FormRequest;

class ClaimSendToMinstroy extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'guid' => 'required',
            'comment' => 'required',
        ];
    }
}
