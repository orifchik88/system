<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RegulationDemandResource;
use App\Models\Regulation;
use App\Models\RegulationDemand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ViolationController extends BaseController
{
    public function actViolations(): JsonResponse
    {
        $type = request('type');

        try {
            $regulation = Regulation::findOrFail(request('regulation_id'));

            $actViolations = $regulation->actViolations()
                ->orderBy('violation_id')
                ->with('violation')
                ->get();

            $demands = $regulation->demands()
                ->where('act_violation_type_id', $type)
                ->with(['actViolation.violation'])
                ->get();

            $sortedDemands = $demands->sortBy(function ($demand) use ($actViolations) {
                return [
                    array_search($demand->act_violation_id, $actViolations->pluck('id')->toArray()),
                    $demand->created_at
                ];
            });

            return $this->sendSuccess(RegulationDemandResource::collection($sortedDemands->values()), 'Act violations');

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage());
        }
    }

//    public function actViolations()
//    {
//        try {
//            $regulation = Regulation::findOrFail(request('regulation_id'));
//            $actViolations = $regulation->actViolations()
//                ->orderBy('violation_id')
//                ->orderBy('created_at')
//                ->get();
//
//            $actViolationIds = $actViolations->pluck('id')->toArray();
//
//            $orderByClause = 'CASE';
//            foreach ($actViolationIds as $index => $id) {
//                $orderByClause .= " WHEN act_violation_id = {$id} THEN {$index}";
//            }
//            $orderByClause .= ' END';
//
//            $demands = $regulation->demands()
//                ->whereIn('act_violation_id', $actViolationIds)
//                ->with(['actViolation.violation'])
//                ->orderByRaw($orderByClause)
//                ->orderBy('created_at')
//                ->get();
//
//
//
////            $demands = $regulation->demands()
////                ->with(['actViolation.violation'])
////                ->orderBy('created_at')
////                ->get()
////                ->groupBy('act_violation_id');
//
////            $orderedDemands = $demands->flatMap(function ($group) {
////                return $group->sortBy('created_at');
////            });
//
//
//            $dd = RegulationDemandResource::collection($demands);
//            return response()->json($dd);
////            $actViolations = $regulation->actViolations()
////                ->with(['demands' => function($query) {
////                    $query->orderBy('act_violation_id')->orderBy('created_at');
////                }])
////                ->get();
////            return response()->json($actViolations);
//
//
//        }catch (\Exception $exception){
//            return $this->sendError($exception->getMessage());
//        }
//    }
}
