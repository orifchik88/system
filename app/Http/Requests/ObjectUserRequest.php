<?php

namespace App\Http\Requests;

use App\Enums\UserStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ObjectUserRequest extends FormRequest
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
            'object_id' => 'required|integer|exists:articles,id',
            'role_id' => 'required|integer|exists:roles,id',
            'name' => 'sometimes|string',
            'middle_name' => 'sometimes|string',
            'surname' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'pinfl' => 'required|string|unique:users,pinfl',
            'organization_name' => 'sometimes',
            'image' => 'sometimes',
            'files' => "sometimes",
            'inn' => 'sometimes',
            'created_by' => "required|exists:users,id",
            'region_id' => 'sometimes|integer|exists:regions,id',
            'district_id' => 'sometimes|integer|exists:districts,id',
            "user_status_id" => "required|exists:user_statuses,id",
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'user_status_id' => UserStatusEnum::ACTIVE->value,
            'created_by' => Auth::id(),
        ]);
    }
}
