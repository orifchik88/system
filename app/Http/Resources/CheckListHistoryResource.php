<?php

namespace App\Http\Resources;

use App\Models\Violation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckListHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//        $violations = null;
//        $regulationNumbers = null;
//        $additionalInfo = $this->content->additionalInfo ?? (object) [];
//
//        if (!empty($additionalInfo->violations)) {
//            $violations = Violation::query()->whereIn('id', $additionalInfo->violations)->get();
//
//            $regulationNumbers = $violations->flatMap(function ($violation) {
//                return $violation->regulations->pluck('regulation_number');
//            })->unique();
//
//        }

        return [
            'id'=> $this->id,
            'user' => UserResource::make($this->user),
            'role' => RoleResource::make($this->role),
//            'comment' => $this->content->comment ?? '',
            'date' => $this->content->date ?? '',
            'status' => $this->content->status ?? '',
//            'violations' => ViolationResource::collection($violations ?: collect()),
//            'regulation_numbers' => $regulationNumbers ? $regulationNumbers->values() : null,
            'user_answered' => $additionalInfo->user_answered ?? null,
            'answered' => $additionalInfo->answered ?? 'manual',
//            'images' => ImageResource::collection($this->images),
//            'files' => DocumentResource::collection($this->documents)
        ];
    }
}
