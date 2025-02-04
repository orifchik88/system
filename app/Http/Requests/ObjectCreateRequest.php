<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'region_id' => 'required|integer|exists:regions,id',
            'district_id' => 'required|integer|exists:districts,id',
            'organization_name' => 'required|',
            'object_type_id' => 'required|integer|exists:object_types,id',
            'deadline' => 'required|string',
            'cadastral_number' => 'required|string',
            'difficulty_category_id' => 'required|integer|exists:difficulties_categories,id',
            'construction_cost' => 'required|string',
            'address' => 'required|string',
            'name_expertise' => 'required|string',
            'task_id' => 'required|integer',
            'number_protocol' => 'required|integer',
            'gnk_id' => 'required|string',
            'funding_source_id' => 'required|integer|exists:funding_sources,id',
        ];
    }
}
