<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\DistrictResource;
use App\Http\Resources\RegionResource;
use App\Models\District;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RegionController extends BaseController
{
    public function regions(): JsonResponse
    {
        return $this->sendSuccess(RegionResource::collection(Region::all()), 'All Regions');
    }

    public function districts($id = null): JsonResponse
    {
        try {
           $region = Region::query()->findOrFail($id);
           return $this->sendSuccess(DistrictResource::collection($region->districts), 'All Districts');
        }catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }
}
