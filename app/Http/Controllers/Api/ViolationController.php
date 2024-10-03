<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActViolationResource;
use App\Http\Resources\RegulationDemandResource;
use App\Http\Resources\RegulationViolationResource;
use App\Http\Resources\ViolationResource;
use App\Models\ActViolation;
use App\Models\Regulation;
use App\Models\RegulationDemand;
use App\Models\RegulationViolation;
use App\Models\Violation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ViolationController extends BaseController
{
    public function actViolations(): JsonResponse
    {
        try {
            $regulation = Regulation::findOrFail(request('regulation_id'));
            $actViolations = $regulation->actViolations;
            return $this->sendSuccess(ActViolationResource::collection($actViolations), 200);

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage());
        }
    }

    public function violations(): JsonResponse
    {
        try {
            $regulation = Regulation::query()->findOrFail(request('regulation_id'));


            return $this->sendSuccess(RegulationViolationResource::collection($regulation->regulationViolations), 'Violations');

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }


}
