<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            "phone" => "string",
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            "pinfl" => "required|integer|digits:14",
            "user_status_id" => "required|exists:user_statuses,id",
            "doc_number" => "string",
            "diplom" => "file",
            "objective" => "file",
            "attestation" => "string",
            "nps" => "string",
            "surname" => "string",
            "middle_name" => "string",
            "address" => "string",
            "passport_number" => "string",
            "region_id" => "required|exists:regions,id",
            "district_id" => "required|exists:districts,id",
            "organization_name" => "string",
            "company_id" => "integer",
            "user_type_id" => "exists:user_types,id",
            "is_activated" => "boolean",
            "shq_id" => "integer",
            "stir_org" => "string",
            "datenm_contract" => "string",
            "name_graduate_study" => "string",
            "diplom_number" => "string",
            "date_issue_diploma" => "string",
            "certificate_courses" => "string",
            "specialization" => "string",
            "role_in_object" => "string",
            "role_id" => "integer",
        ];
    }
}
