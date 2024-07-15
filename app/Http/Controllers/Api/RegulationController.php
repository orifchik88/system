<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MonitoringResource;
use App\Models\Monitoring;
use Illuminate\Http\Request;

class RegulationController extends BaseController
{
    public function monitoring()
    {
        $monitorings = Monitoring::where('object_id', \request('object_id'))->get();
        return $this->sendSuccess(MonitoringResource::collection($monitorings), 'Monitorings');
    }
}
