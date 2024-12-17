<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckListHistoryListResource extends JsonResource
{

    public function toArray(Request $request): array
    {

        $additionalInfo = $this->content->additionalInfo ?? (object) [];


        return [
            'id'=> $this->id,
            'user' => UserResource::make($this->user),
            'role' => RoleResource::make($this->role),
            'comment' => $this->content->comment ?? '',
            'date' => $this->content->date ?? '',
            'status' => $this->content->status ?? '',
            'user_answered' => $additionalInfo->user_answered ?? null,
            'answered' => $additionalInfo->answered ?? 'manual',
            'images' => ImageResource::collection($this->images) ?? null,
            'files' => DocumentResource::collection($this->documents) ?? null
        ];
    }
}
