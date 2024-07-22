<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MonitoringResource;
use App\Http\Resources\RegulationResource;
use App\Http\Resources\ViolationResource;
use App\Models\Monitoring;
use App\Models\Regulation;
use App\Models\Violation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class RegulationController extends BaseController
{
    public function monitoring(): JsonResponse
    {
        $monitorings = Monitoring::query()->where('object_id', \request('object_id'))->paginate(request('per_page', 10));
        return $this->sendSuccess(MonitoringResource::collection($monitorings), 'Monitorings', pagination($monitorings));
    }

    public function regulations(): JsonResponse
    {
        try {
            $user = Auth::user();
            if(request('id')){
                return $this->sendSuccess(RegulationResource::make( $user->regulations()->findOrFail(request('id'))), 'Regulation');
            }

            $query = $user->regulations();

            if (request()->boolean('is_state')) {
                $query->withInspectorRole();
            } else {
                $query->withoutInspectorRole();
            }

            $regulations = $query->paginate(request('per_page', 10));

            return $this->sendSuccess(
                RegulationResource::collection($regulations),
                'Regulations',
                pagination($regulations)
            );

        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function violation(): JsonResponse
    {
        try {
            $violation = Violation::query()->findOrFaiL(request('id'));
            return $this->sendSuccess(ViolationResource::make($violation), 'Violation');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function test()
    {
       $violation =  Violation::find(request('id'));

       dd($violation);
    }
}
