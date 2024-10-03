<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            $actViolations = $regulation->actViolations()
                ->orderBy('violation_id')
                ->orderBy('created_at')
                ->get();

            $actViolationIds = $actViolations->pluck('id')->toArray();

            $orderByClause = 'CASE';
            foreach ($actViolationIds as $index => $id) {
                $orderByClause .= " WHEN act_violation_id = {$id} THEN {$index}";
            }
            $orderByClause .= ' END';

            $demands = $regulation->demands()
                ->where('act_violation_type_id', request('type'))
                ->when(request('type') != 3, function ($query) use ($orderByClause, $actViolationIds) {
                    return $query->whereIn('act_violation_id', $actViolationIds)
                                 ->with(['actViolation.violation'])
                                 ->orderByRaw($orderByClause);
                })
                ->orderBy('created_at')
                ->paginate(request('per_page', 10));

            return $this->sendSuccess(RegulationDemandResource::collection($demands), 'Act violations', pagination($demands));

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

    public function getViolation($id): JsonResponse
    {
        try {
            $violation = RegulationViolation::query()->findOrFail($id);
            return $this->sendSuccess(new RegulationViolationResource($violation), 'Violation');

        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage());
        }

    }
}
