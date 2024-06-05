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
            "project_id" => "integer",
            "client_platform_id" => "integer",
            "client_type_id" => "required|exists:client_types,id",
            "phone" => "string",
            "login" => "required|string",
            "password" => "required|string",
            "active" => "integer",
            "image" => "string",
            "pinfl" => "required|string",
            "user_status_id" => "required|exists:user_statuses,id",
            "doc_number" => "string",
            "diplom" => "string",
            "objective" => "string",
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
            "user_type_id" => "required|exists:user_types,id",
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
        ];
    }
}
