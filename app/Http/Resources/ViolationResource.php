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
    public function __construct($resource, public ?int $regulation_id)
    {
        parent::__construct($resource);
    }

    public static function collection($resource, $regulation_id = null)
    {
        return tap(new ViolationResourceCollection($resource, $regulation_id), function ($collection) {
            $collection->collects = static::class;
        });
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $violation = $this->resource;

        if (!$violation) {
            return [];
        }

        $array = RegulationViolationBlock::query()
            ->where('regulation_id', $this->regulation_id)
            ->where('violation_id', $violation->id)
            ->pluck('id', 'block_id');

        if ($array->isEmpty()){
            throw  new NotFoundException('Violation not found');
        }

        $blocks = $array->map(function ($value, $key) {
            return new BlockResource(Block::query()->find($key), $value);
        })->values();


        return [
            'id' => $violation->id,
            'title' => $violation->title,
            'description' => $violation->description,
            'level' => LevelResource::make($violation->level),
            'check_list_status' => true,
            'blocks' => $blocks,
        ];
    }
}
