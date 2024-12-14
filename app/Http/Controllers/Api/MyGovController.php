<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MyGovService;
use Illuminate\Support\Facades\Auth;

class MyGovController extends Controller
{
    private MyGovService $myGovService;

    public function __construct(MyGovService $myGovService)
    {
        $this->myGovService = $myGovService;
    }

    public function showTask($id)
    {
        $data = $this->myGovService->getDxaTaskById(task_id: $id);

        if (!$data) {
            return ['success' =>  false, 'message' => "Ma'lumot topilmadi"];
        }

        return $data;
    }

    public function getObjectsByPinfl()
    {
        $data = $this->myGovService->getObjectsByPinfl(pinfl: request()->get('pinfl'));

        if (!$data) {
            return ['success' =>  false, 'message' => "Ma'lumot topilmadi"];
        }

        return $data;
    }

    public function getObjectsByCustomer()
    {
        $data = $this->myGovService->getObjectsByCustomer(pinfl: request()->get('pinfl'));

        if (!$data) {
            return ['success' =>  false, 'message' => "Ma'lumot topilmadi"];
        }

        return $data;
    }

    public function getObjectsByCadastr()
    {
        $data = $this->myGovService->getObjectsByCadastralNumber(request('cadastr'));

        if (!$data) {
            return ['success' =>  false, 'message' => "Ma'lumot topilmadi"];
        }

        return $data;
    }
}
