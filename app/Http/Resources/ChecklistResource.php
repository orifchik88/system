<?php

namespace App\Http\Resources;

use App\Models\CheckListHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChecklistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'logs' => CheckListHistoryResource::collection($this->logs),
        ];
    }
}
