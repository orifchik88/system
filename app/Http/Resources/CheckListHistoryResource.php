<?php

namespace App\Http\Resources;

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
        return [
            'id'=> $this->id,
            'user_id' => $this->content->user,
            'comment' => $this->content->comment,
            'date' => $this->content->date,
            'status' => $this->content->status,
            'image' => ImageResource::collection($this->images),
            'files' => DocumentResource::collection($this->documents)
        ];
    }
}
