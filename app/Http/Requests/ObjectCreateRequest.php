<?php

namespace App\Http\Requests;

use App\Enums\ObjectStatusEnum;
use App\Models\Article;
use App\Models\Region;
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
            'cadastral_number' => 'required_if:object_type_id,2',
            'difficulty_category_id' => 'required|integer|exists:difficulty_categories,id',
            'construction_cost' => 'required|string',
            'construction_works' => 'required|string',
            'address' => 'required|string',
            'name_expertise' => 'required|string',
            'manual_task_id' => 'required|string',
            'number_protocol' => 'required|integer',
            'funding_source_id' => 'required|integer|exists:funding_sources,id',
            'linear_type' => 'required_if:object_type_id,1',
            'sphere_id' => 'required|integer|exists:spheres,id',
            'users.*' => 'required|array',
            'blocks.*' => 'required|array',
            'files' => 'required|array',
            'expertise_files' => 'required|array',
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
            'funding_source_id' => 2,
            'manual_task_id' => $this->generateUniqueTaskId()
        ]);
    }

    private function generateUniqueTaskId(): string
    {
        $regionCode = Region::query()
            ->where('id', $this->region_id)
            ->value('region_code');

        if (!$regionCode) {
            throw new \Exception("Viloyat topilmadi");
        }

        do {
            $randomNumber = rand(100000, 999999);
            $taskId = '0'.$regionCode . $randomNumber;
        } while (Article::query()->where('manual_task_id', $taskId)->exists());

        return $taskId;
    }
}
