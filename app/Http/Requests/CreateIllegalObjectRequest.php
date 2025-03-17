<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateIllegalObjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'lat' => 'required',
            'long' => 'required',
            'address' => 'required',
            'district_id' => 'required',
            'inn' => 'required',
            'organization_name' => 'required',
            'images.*' =>  'required|image|mimes:jpeg,png,jpg,gif',
        ];
    }
}
