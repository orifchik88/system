<?php

namespace App\Http\Requests\ClaimRequests;

use Illuminate\Foundation\Http\FormRequest;

class SendToDirector extends FormRequest
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
            'id' => 'required',
            'buildings' => 'required|array|min:1',
            'buildings.*.building_name' => 'required',
            'buildings.*.building_cadastr' => 'required',
            'buildings.*.building_count' => 'required|numeric|min:1',
            'buildings.*.building_all_field' => 'required',
            'buildings.*.building_used_field' => 'required',
            'buildings.*.building_living_field' => 'required',
            'buildings.*.building_backend_field' => 'required',
        ];
    }
}
