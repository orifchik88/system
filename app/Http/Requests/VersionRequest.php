<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VersionRequest extends FormRequest
{

    public function authorize(): bool
    {
        return false;
    }


    public function rules(): array
    {
        return [
            'version' => 'string|required',
            'app_link' => 'string|required',
            'type' => 'integer|required',
        ];
    }
}
