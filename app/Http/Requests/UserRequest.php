<?php

namespace App\Http\Requests;

use App\Enums\UserStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserRequest extends FormRequest
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
            "name" => "required|string",
            "phone" => "required|string|unique:users,phone",
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            "pinfl" => "required|integer|digits:14|unique:users,pinfl",
            "user_status_id" => "required|exists:user_statuses,id",
            "surname" => "string",
            "middle_name" => "string",
            "region_id" => "required|exists:regions,id",
            "district_id" => "required|exists:districts,id",
            "role_ids" => "required|array",
            "role_ids.*" => "required|integer|exists:roles,id",
            'created_by' => "required|exists:users,id",
            'type' => "required|integer",
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'user_status_id' => UserStatusEnum::ACTIVE->value,
            'created_by' => Auth::id(),
            'type' => 1
        ]);
    }
}
