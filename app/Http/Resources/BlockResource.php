<?php

namespace App\Http\Resources;

use App\Models\RegulationViolationBlock;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockResource extends JsonResource
{

    public function __construct($resource, public int $regulationViolationBlockId)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $regulationViolationBlock = RegulationViolationBlock::query()->find($this->regulationViolationBlockId);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
        ];
    }
}
