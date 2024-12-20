<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateIllegalObjectRequest;
use App\Http\Requests\UpdateCheckListRequest;
use App\Models\District;
use App\Services\IllegalObjectService;
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
        $response = $this->illegalObjectService->updateCheckList(request: $request);

        if ($response) {
            return $this->sendSuccess($response, 'Success');
        } else {
            return $this->sendError("API ERROR", "message");
        }
    }

    public function createObject(CreateIllegalObjectRequest $request)
    {
        $response = $this->illegalObjectService->createObject($request);

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

        return $this->sendSuccess($data, 'Successfully sent!');
    }

    public function objectsList()
    {
        $id = request()->get('id', null);
        $sortBy = request()->get('sort_by_date', 'desc');
        $status = request()->get('status', null);
        $roleId = Auth::user()->getRoleFromToken() ?? null;

        $regionId = match ($roleId) {
            (string)UserRoleEnum::INSPEKSIYA->value,
            (string)UserRoleEnum::OPERATOR->value, (string)UserRoleEnum::INSPECTOR->value => Auth::user()->region_id ?? Auth::user()->region_id ?? null,
            default => request()->get('region_id', null),
        };

        $districtId = request()->get('district_id', null);

        $data = $this->illegalObjectService->getObjectList(
            regionId: $regionId,
            id: $id,
            districtId: $districtId,
            sortBy: $sortBy,
            status: $status,
            role_id: (in_array($roleId, [31,32,33])) ? $roleId : null
        );


        return $this->sendSuccess($data->items(), 'Successfully sent!', pagination($data));
    }
}
