<?php

namespace App\Http\Requests;

use App\Enums\ObjectStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class HolidayCreateRequest extends FormRequest
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
            'day' => 'required|unique:holidays,day',
            'role_id' => 'required|integer',
            'user_id' => 'required|integer',
            'status' => 'required|boolean'
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'status' => true,
            'user_id' => Auth::id()
        ]);
    }
}
