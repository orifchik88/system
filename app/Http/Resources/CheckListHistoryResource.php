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
        $violations = null;
        $additionalInfo = $this->content->additionalInfo ?? [];

        if (!empty($additionalInfo['violations'])) {
            $violations = Violation::query()->whereIn('id', $additionalInfo['violations'])->get();
        }
        return [
            'id'=> $this->id,
            'user' => UserResource::make($this->user),
            'role' => RoleResource::make($this->role),
            'comment' => $this->content->comment,
            'date' => $this->content->date,
            'status' => $this->content->status,
            'violations' => ViolationResource::collection($violations ?: collect()),
            'user_answered' => $additionalInfo['user_answered'] ?? null,
            'image' => ImageResource::collection($this->images),
            'files' => DocumentResource::collection($this->documents)
        ];
    }
}
