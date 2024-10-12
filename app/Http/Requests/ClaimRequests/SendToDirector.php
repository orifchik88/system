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
            'buildings.*.name' => 'required',
            'buildings.*.cadaster' => 'required',
            'buildings.*.building_number' => 'required|numeric|min:1',
            'buildings.*.total_area' => 'required',
            'buildings.*.total_use_area' => 'required',
            'buildings.*.living_area' => 'required',
            'buildings.*.area' => 'required',
        ];
    }
}
