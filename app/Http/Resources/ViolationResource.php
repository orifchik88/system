<?php

namespace App\Http\Resources;

use App\Models\Block;
use App\Models\RegulationViolationBlock;
use App\Models\Violation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ViolationResource extends JsonResource
{
    public function __construct($resource, public int $regulation_id)
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
        $array = RegulationViolationBlock::query()
            ->where('regulation_id', $this->regulation_id)
            ->where('violation_id', $this->resource->first()->id)
            ->pluck('id', 'block_id');


        $blocks = $array->map(function ($value, $key) {
            return new BlockResource(Block::query()->find($key), $value);
        })->values();




        return [
            'id' => $this->resource->first()->id,
            'title' => $this->resource->first()->title,
            'description' => $this->resource->first()->description,
            'level' => LevelResource::make($this->resource->first()->level),
            'check_list_status' => true,
            'blocks' => $blocks,
        ];
    }
}
