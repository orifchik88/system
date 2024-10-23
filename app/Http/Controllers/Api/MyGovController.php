<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MyGovService;
use Illuminate\Support\Facades\Auth;

class MyGovController extends BaseController
{
    private MyGovService $myGovService;

    public function __construct(MyGovService $myGovService)
    {
        $this->middleware('auth');
        parent::__construct();
        $this->myGovService = $myGovService;
    }

    public function showTask($id)
    {
        $data = $this->myGovService->getDxaTaskById(task_id: $id);

        if (!$data) {
            return $this->sendError("Ma'lumot topilmadi!", [], 422);
        }

        return $this->sendSuccess($data, 'Success!');
    }

    public function getObjectsByPinfl()
    {
        $data = $this->myGovService->getObjectsByPinfl(pinfl: request()->get('pinfl'));

        if (!$data) {
            return $this->sendError("Ma'lumot topilmadi!", [], 422);
        }

        return $this->sendSuccess($data, 'Success!');
    }
}
