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
            'user' => UserResource::make($this->user),
            'role' => RoleResource::make($this->role),
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