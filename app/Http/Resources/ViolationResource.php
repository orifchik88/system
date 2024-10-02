<?php

namespace App\Http\Resources;

use App\Exceptions\NotFoundException;
use App\Models\Block;
use App\Models\RegulationViolationBlock;
use App\Models\Violation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ViolationResource extends JsonResource
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
            'title' => $this->title,
            'question_id' => $this->question_id,
            'comment' => $this->description,
        ];
    }
}
