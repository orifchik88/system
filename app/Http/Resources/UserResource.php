<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    public function __construct($resource, public  $permissions = null)
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
        return [
            'id'=>$this->id,
            'surname' => $this->surname,
            'name' => $this->name,
            'middle_name' => $this->middle_name,
            'address' => $this->address,
            'organization_name' => $this->organization_name,
            'phone'=> $this->phone,
            'nps'=> $this->nps,
            'login' => $this->login,
            'image' => $this->image ? Storage::disk('public')->url($this->image) : null,
            'permits' => $this->permissions,
        ];
    }
}
