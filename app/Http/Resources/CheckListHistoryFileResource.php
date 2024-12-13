<?php

namespace App\Http\Resources;

use App\Models\Violation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckListHistoryFileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $violations = null;
        $regulationNumbers = null;
        $additionalInfo = $this->content->additionalInfo ?? (object) [];

        if (!empty($additionalInfo->violations)) {
            $violations = Violation::query()->whereIn('id', $additionalInfo->violations)->get();

            $regulationNumbers = $violations->flatMap(function ($violation) {
                return $violation->regulations->pluck('regulation_number');
            })->unique();

        }

        return [
            'id'=> $this->id,
            'comment' => $this->content->comment ?? '',
            'violations' => ViolationResource::collection($violations ?: collect()),
            'regulation_numbers' => $regulationNumbers ? $regulationNumbers->values() : null,
            'images' => ImageResource::collection($this->images),
            'files' => DocumentResource::collection($this->documents)
        ];
    }
}
