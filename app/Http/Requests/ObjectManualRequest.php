<?php

namespace App\Http\Requests;

use App\Enums\ObjectStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ObjectManualRequest extends FormRequest
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
            'object_name' => 'required',
            'region_id' => 'required',
            'district_id' => 'required',
            'object_type_id' => 'required',
            'organization_name' => 'required',
            'location_building' => 'required',

        ];
    }
    public function prepareForValidation()
    {
        $this->merge([
//            'created_by' => Auth::guard('api')->user()->id,
            'object_status_id' => ObjectStatusEnum::PROGRESS,
        ]);
    }

}
