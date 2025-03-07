<?php

namespace App\Http\Resources;

use App\Models\RegulationViolationBlock;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlockResource extends JsonResource
{

    public function __construct($resource, public ?int $regulationViolationBlockId = null)
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
//        $images = [];
//        if ($this->regulationViolationBlockId) {
//            $regulationViolationBlock = RegulationViolationBlock::query()->find($this->regulationViolationBlockId);
//            if ($regulationViolationBlock) {
//                $images = ImageResource::collection($regulationViolationBlock->images);
//            }
//        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
//            'comment' => $comment,
            'files' => DocumentResource::collection($this->documents),
            'images' => ImageResource::collection($this->images),
        ];
    }
}
