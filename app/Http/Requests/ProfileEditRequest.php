<?php

namespace App\Http\Requests;

use App\Exceptions\GeneralJsonException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ProfileEditRequest extends FormRequest
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
            'phone' => 'nullable|regex:/^998\d{9}$/',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
        ];
    }

    public function messages()
    {
        return [
            'phone.required' => 'Telefon raqami kiritilishi shart.',
            'phone.regex' => 'Telefon raqami 998 bilan boshlanib, keyin 9 ta raqamdan iborat bo\'lishi kerak.',
            'image.image' => 'Fayl rasm bo\'lishi kerak.',
            'image.mimes' => 'Rasm faqat jpeg, png, jpg, gif, svg formatlarida bo\'lishi mumkin.',
            'image.max' => 'Rasm hajmi maksimal 2MB bo\'lishi mumkin.'
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new GeneralJsonException($validator->errors(), 422);
    }
}
