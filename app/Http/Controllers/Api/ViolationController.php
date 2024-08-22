<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RegulationDemandResource;
use App\Http\Resources\ViolationResource;
use App\Models\ActViolation;
use App\Models\Regulation;
use App\Models\RegulationDemand;
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
                ->with(['actViolation.violation'])
                ->when(request('type') != 3, function ($query) use ($orderByClause, $actViolationIds) {
                    return $query->whereIn('act_violation_id', $actViolationIds)
                                 ->orderByRaw($orderByClause);
                })
                ->orderBy('created_at')
                ->paginate(request('per_page', 10));;

           return $this->sendSuccess(RegulationDemandResource::collection($demands), 'Act violations', pagination($demands));

        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage());
        }
    }

    public function violations(): JsonResponse
    {
        try {
            if (request('id')  && request('regulation_id')) {
                $violation = Violation::query()->findOrFail(request('id'));
                return $this->sendSuccess(new ViolationResource($violation, \request('regulation_id')), 'Violation');
            }
            if (request('regulation_id')) {
                $regulation = Regulation::query()->findOrFail(request('regulation_id'));
                $violations = $regulation->violations()->paginate(request('per_page', 10));
                return $this->sendSuccess(ViolationResource::collection($violations, $regulation->id), 'Violations', pagination($violations));
            }
            return $this->sendSuccess([], 'Violations not found');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }
}
