<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ViolationResourceCollection extends ResourceCollection
{

    public function __construct($resource, public ?int $regulation_id)
    {
        parent::__construct($resource);
    }

    public function toArray($request): array
    {
        return $this->collection->map(function ($item) {
            return new ViolationResource($item, $this->regulation_id);
        })->all();
    }
}
