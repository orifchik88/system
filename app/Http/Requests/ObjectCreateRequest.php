<?php

namespace App\Http\Requests;

use App\Enums\ObjectStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ObjectCreateRequest extends FormRequest
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
            'lat'=>'required|string',
            'long'=>'required|string',
            'object_status_id'=>'required|integer|exists:object_statuses,id',
            'region_id' => 'required|integer|exists:regions,id',
            'district_id' => 'required|integer|exists:districts,id',
            'organization_name' => 'required|',
            'object_type_id' => 'required|integer|exists:object_types,id',
            'deadline' => 'required|string',
            'cadastral_number' => 'required|string',
            'difficulty_category_id' => 'required|integer|exists:difficulty_categories,id',
            'construction_cost' => 'required|string',
            'construction_works' => 'required|string',
            'address' => 'required|string',
            'name_expertise' => 'required|string',
            'task_id' => 'required|integer',
            'number_protocol' => 'required|integer',
            'funding_source_id' => 'required|integer|exists:funding_sources,id',
            'linear_type' => 'required_if:object_type_id,1|string|exists:object_types,id',
            'sphere_id' => 'required|integer|exists:spheres,id',
            'users.*' => 'required|array',
            'blocks.*' => 'required|array',
            'files' => 'sometimes|array',
            'expertise_files' => 'sometimes|array',
            'price_supervision_service' => 'required',
            'inspector_id' => 'required|integer|exists:users,id',
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'price_supervision_service' => price_supervision($this->construction_cost),
            'object_status_id' => ObjectStatusEnum::PROGRESS->value,
            'appearance_type_id' => 1,
        ]);
    }
}
