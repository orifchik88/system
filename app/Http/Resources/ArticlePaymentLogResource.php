<?php

namespace App\Http\Resources;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticlePaymentLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=> $this->id,
            'user' => $this->user_id ? UserResource::make($this->user) : null,
            'role' =>  $this->role_id ? RoleResource::make($this->role) : null,
            'comment' => $this->content->comment ?? '',
            'date' => $this->content->date ?? '',
            'status' => $this->content->status ?? '',
            'amount' => $this->content->additionalInfo->amount ?? 0,
            'cost' => $this->content->additionalInfo->cost ?? 0,
            'files' => DocumentResource::collection($this->documents),
            'images' => ImageResource::collection($this->images),
        ];
    }
}
