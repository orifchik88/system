<?php

namespace App\Http\Requests;

use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;
use function Symfony\Component\Translation\t;

class ObjectPriceRequest extends FormRequest
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
            'object_id' => [
                'required',
                'integer',
                'exists:articles,id',
//                function ($attribute, $value, $fail) {
//                    $cost = Article::query()->where('id', $value)->value('construction_cost');
//                    if (intval($cost) !== 0) {
//                        $fail('Tanlangan obyektning qiymati mavjud');
//                    }
//                },
            ],
            'price' => 'required',
        ];
    }
}
