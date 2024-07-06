<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserResourceCollection extends ResourceCollection
{
    public function __construct($resource, public ?array $permissions = null)
    {
        parent::__construct($resource);
    }
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray($request)
    {

        return [
            'data' => $this->collection->map(function ($user) use($request) {
                return (new UserResource($user, $this->permissions))->toArray($request);
            }),
        ];
    }
}
