<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SphereResource;
use App\Models\Sphere;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SphereController extends BaseController
{
    public function spheres(): JsonResponse
    {
        try {
            $spheres = Sphere::query()->paginate(request('per_page', 10));
            return $this->sendSuccess(SphereResource::collection($spheres), 'All Spheres', pagination($spheres));
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage());
        }
    }
}
