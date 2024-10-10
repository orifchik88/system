<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\Role;
use App\Services\ClaimService;
use App\Services\HistoryService;
use Illuminate\Support\Facades\Auth;

class ClaimController extends BaseController
{

    private ClaimService $claimService;
    private HistoryService $historyService;

    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
        $this->historyService = new HistoryService('claim_histories');
    }

    public function statisticsQuantity()
    {
        $dateFrom = request()->get('date_from', null);
        $dateTo = request()->get('date_to', null);

        $roleId = Auth::user()->getRoleFromToken() ?? null;

        $regionId = match ($roleId) {
            (string)UserRoleEnum::OPERATOR->value, (string)UserRoleEnum::INSPECTOR->value => Auth::user()->region_id ?? Auth::user()->region_id ?? null,
            default => request()->get('region', null),
        };


        $data = $this->claimService->getStatisticsCount(
            regionId: $regionId,
            expired: null,
            dateFrom: $dateFrom,
            dateTo: $dateTo
        );

        return $this->sendSuccess($data, 'Successfully sent!');
    }

    public function getPDF()
    {
        $result = $this->claimService->getPDF(request()->get('task_id'));
        return $this->sendSuccess($result, 'Success');
    }

    public function getConclusionPDF()
    {
        $object = $this->claimService->getClaimByGUID(request()->get('task_id'));
        if (!$object)
            $this->sendError("Bunday ma'lumot mavjud emas");

        $result = $this->claimService->getConclusionPDF(request()->get('task_id'));
        if (isset($result['status']))
            return $this->sendError("Xulosa topilmadi");

        return $this->sendSuccess($result, 'Success');
    }

    public function tasksHistories($id)
    {
        $data = $this->historyService->getHistoryList(
            intval($id)
        );

        return $this->sendSuccess($data, 'Success');
    }

    public function showTask($id)
    {
        $data = $this->claimService->getClaimById(
            id: $id
        );

        if (!$data) {
            return $this->sendError("Tizimda xatolik", [], 422);
        }

        return $this->sendSuccess($data, 'Success!');
    }

    public function tasksList()
    {
        $main = request()->get('main', null);
        $dateFrom = request()->get('date_from', null);
        $dateTo = request()->get('date_to', null);
        $status = request()->get('status', null);
        $expired = request()->get('expired', 0);

        $roleId = Auth::user()->getRoleFromToken() ?? null;

        $regionId = match ($roleId) {
            (string)UserRoleEnum::OPERATOR->value, (string)UserRoleEnum::INSPECTOR->value => Auth::user()->region_id ?? Auth::user()->region_id ?? null,
            default => request()->get('region', null),
        };

        $data = $this->claimService->getTaskList(
            regionId: $regionId,
            main: $main,
            dateFrom: $dateFrom,
            dateTo: $dateTo,
            status: $status,
            expired: $expired,
        );

        return $this->sendSuccess($data->items(), 'Successfully sent!', pagination($data));
    }
}
