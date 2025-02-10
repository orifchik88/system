<?php

namespace App\Http\Controllers\Api;

use App\Enums\ConstructionWork;
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

    public function getObjectsList()
    {
        $filters = request()->only(['date_from', 'date_to', 'regions', 'stir', 'page', 'per_page']);

        $data = $this->myGovService->getObjectList($filters);

        if (!$data) {
            return ['success' =>  false, 'message' => "Ma'lumot topilmadi"];
        }
        return $data;
    }

    public function getObjectsByOrganization()
    {

        $data = $this->myGovService->getObjectOrganization();

        if (!$data) {
            return ['success' =>  false, 'message' => "Ma'lumot topilmadi"];
        }

        return $data;
    }

    public function getObjectsByDesign()
    {
        $data = $this->myGovService->getObjectDesign();

        if (!$data) {
            return ['success' =>  false, 'message' => "Ma'lumot topilmadi"];
        }

        return $data;
    }

    public function getObjectsRegulations()
    {
        $filters = request()->only(['gnk_id', 'protocol_number', 'reestr_number']);

        $data = $this->myGovService->getObjectsRegulations($filters);

        if (!$data) {
            return ['success' =>  false, 'message' => "Ma'lumot topilmadi"];
        }

        return $data;
    }



}
