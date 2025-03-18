<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateIllegalObjectRequest;
use App\Http\Requests\IllegalObjectUpdateRequest;
use App\Http\Requests\UpdateCheckListRequest;
use App\Models\District;
use App\Services\IllegalObjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class IllegalObjectController extends BaseController
{
    private IllegalObjectService $illegalObjectService;

    public function __construct(IllegalObjectService $illegalObjectService)
    {
        $this->middleware('auth');
        parent::__construct();
        $this->illegalObjectService = $illegalObjectService;
    }

    public function updateCheckList(UpdateCheckListRequest $request)
    {
        $response = $this->illegalObjectService->updateCheckList(request: $request, user: $this->user, roleId: $this->roleId);

        if ($response) {
            return $this->sendSuccess($response, 'Success');
        } else {
            return $this->sendError("API ERROR", "message");
        }
    }

    public function createObject(CreateIllegalObjectRequest $request): JsonResponse
    {
        $response = $this->illegalObjectService->createObject($request, $this->user, $this->roleId);

        if ($response) {
            return $this->sendSuccess($response, 'Success');
        } else {
            return $this->sendError("API ERROR", "message");
        }
    }

    public function updateObject(IllegalObjectUpdateRequest $request): JsonResponse
    {
        $response = $this->illegalObjectService->updateObject($request, $this->user, $this->roleId);
        if ($response) {
            return $this->sendSuccess($response, 'Success');
        } else {
            return $this->sendError("API ERROR", "message");
        }

    }

    public function saveObject($id)
    {
        $response = $this->illegalObjectService->saveObject($id);

        if ($response) {
            return $this->sendSuccess($response, 'Success');
        } else {
            return $this->sendError("API ERROR", "message");
        }
    }

    public function districtList()
    {
        $data = District::query()->where('region_id', Auth::user()->region_id)->get();

        return $this->sendSuccess($data, 'Successfully sent!');
    }

    public function questionList($id)
    {
        $data = $this->illegalObjectService->getQuestionList($id);

        return $this->sendSuccess($data, 'Successfully sent!');
    }

    public function getObject($id)
    {
        $data = $this->illegalObjectService->getObject($id);

        if ($data) {
            return $this->sendSuccess($data, 'Success');
        } else {
            return $this->sendError("Obyekt Topilmadi!", "message");
        }
    }

    public function getStatistics()
    {
        $dateFrom = request()->get('date_from', null);
        $dateTo = request()->get('date_to', null);

        $regionId = request()->get('region_id', null);

        $data = $this->illegalObjectService->getStatistics(
            regionId: $regionId,
            dateFrom: $dateFrom,
            dateTo: $dateTo
        );

        return $this->sendSuccess($data, 'Successfully sent!');
    }

    public function objectsList()
    {
        $filters = request()->only(['status', 'id', 'sort_by', 'type', 'role_id', 'region_id', 'district_id']);

        $data = $this->illegalObjectService->getObjectList(
            user: $this->user, roleId: $this->roleId, filters: $filters
        );

        return $this->sendSuccess($data->items(), 'Successfully sent!', pagination($data));
    }

    public function objectHistory($id)
    {
        $data = $this->illegalObjectService->getObjectHistory($id);

        return $this->sendSuccess($data, 'Successfully sent!');
    }

    public function checklistHistory($id)
    {
        $data = $this->illegalObjectService->getChecklistHistory($id);

        return $this->sendSuccess($data, 'Successfully sent!');
    }
}
