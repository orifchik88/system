<?php

namespace App\Http\Resources;

use App\Models\ActViolationBlock;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActBlockResource extends JsonResource
{
    public function __construct($resource, public ?int $actViolationId = null)
    {
        parent::__construct($resource);
    }

    public static function collection($resource,  $actViolationId = null)
    {
        return tap(new ActBlockResourceCollection($resource, $actViolationId), function ($collection) {
            $collection->collects = static::class;
        });
    }

    public function toArray(Request $request): array
    {
        $actViolationBlock  = ActViolationBlock::where('act_violation_id', $this->actViolationId)->where('block_id', $this->id)->first();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'comment' => $this->pivot->comment ?? null,
            'documents' => DocumentResource::collection($actViolationBlock->documents),
        ];
    }
}
