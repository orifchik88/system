<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ActBlockResourceCollection extends ResourceCollection
{
    public function __construct($resource, public ?int $actViolationId)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        return $this->collection->map(function ($item) {
            return new ActBlockResource($item, $this->actViolationId);
        })->all();
    }
}
