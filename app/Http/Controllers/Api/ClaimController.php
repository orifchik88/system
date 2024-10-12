<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRoleEnum;
use App\Http\Requests\ClaimRequests\AcceptTask;
use App\Http\Requests\ClaimRequests\AttachBLockAndOrganization;
use App\Http\Requests\ClaimRequests\AttachObject;
use App\Http\Requests\ClaimRequests\ClaimSendToMinstroy;
use App\Http\Requests\ClaimRequests\ConclusionClaimByDirector;
use App\Http\Requests\ClaimRequests\ConclusionClaimByInspector;
use App\Http\Requests\ClaimRequests\ConclusionOrganization;
use App\Http\Requests\ClaimRequests\rejectClaimByInspector;
use App\Http\Requests\ClaimRequests\RejectClaimByOperator;
use App\Http\Requests\ClaimRequests\RejectFromDirector;
use App\Http\Requests\ClaimRequests\SendToDirector;
use App\Models\Block;
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
        $roleId = Auth::user()->getRoleFromToken() ?? null;

        $data = $this->claimService->getClaimById(
            id: $id,
            role_id: (in_array($roleId, [15, 16, 17, 18, 19])) ? $roleId : null
        );

        if (!$data) {
            return $this->sendError("Tizimda xatolik", [], 422);
        }

        return $this->sendSuccess($data, 'Success!');
    }

    public function tasksList()
    {
        $task_id = request()->get('task_id', null);
        $building_name = request()->get('name', null);
        $customer = request()->get('customer', null);
        $sender = request()->get('sender', null);
        $districtId = request()->get('district', null);
        $sortBy = request()->get('sort_by_date', 'desc');

        $status = request()->get('status', null);
        $expired = request()->get('expired', 0);

        $roleId = Auth::user()->getRoleFromToken() ?? null;

        $regionId = match ($roleId) {
            (string)UserRoleEnum::OPERATOR->value, (string)UserRoleEnum::INSPECTOR->value => Auth::user()->region_id ?? Auth::user()->region_id ?? null,
            default => request()->get('region', null),
        };

        $data = $this->claimService->getTaskList(
            regionId: $regionId,
            task_id: $task_id,
            name: $building_name,
            customer: $customer,
            sender: $sender,
            districtId: $districtId,
            sortBy: $sortBy,
            status: $status,
            expired: $expired,
            role_id: (in_array($roleId, [15, 16, 17, 18, 19])) ? $roleId : null
        );


        return $this->sendSuccess($data->items(), 'Successfully sent!', pagination($data));
    }

    public function sendToMinstroy(ClaimSendToMinstroy $request)
    {

        $response = $this->claimService->sendToMinstroy($request);

        if ($response) {
            return $this->sendSuccess("Yuborildi!", 'Success');
        } else {
            return $this->sendError("API ERROR", [], "message");
        }

    }

    public function acceptTask(AcceptTask $request)
    {

        $response = $this->claimService->acceptTask($request);

        if ($response) {
            return $this->sendSuccess("Yuborildi!", 'Success');
        } else {
            return $this->sendError("API ERROR", [], "message");
        }

    }

    public function getObjects($id)
    {
        $data = $this->claimService->getObjects(
            id: $id
        );

        if (!$data) {
            return $this->sendError("Tizimda xatolik", [], 422);
        }

        return $this->sendSuccess($data, 'Success!');
    }

    public function attachObject(AttachObject $request)
    {
        $response = $this->claimService->attachObject($request);

        if ($response) {
            return $this->sendSuccess("Biriktirildi!", 'Success');
        } else {
            return $this->sendError("API ERROR", [], "message");
        }
    }

    public function attachBlockAndOrganization(AttachBLockAndOrganization $request)
    {
        $blocks = $request['blocks'];
        $errors = [];

        foreach ($blocks as $blockId) {
            $block = Block::find($blockId);

            if (!$block) {
                $errors[] = "Blok $blockId topilmadi.";
            } elseif ($block->status) {
                $errors[] = "Blok $blockId tugallanmagan.";
            }
        }

        if (!empty($errors)) {
            return $this->sendError('Bloklarda hatolik!', $errors, 400);
        }

        $response = $this->claimService->attachBlockAndOrganization($request);

        if ($response) {
            return $this->sendSuccess("Biriktirildi!", 'Success');
        } else {
            return $this->sendError("API ERROR", [], "message");
        }
    }

    public function conclusionOrganization(ConclusionOrganization $request)
    {
        $response = $this->claimService->conclusionOrganization($request);

        if ($response) {
            return $this->sendSuccess($response, 'Success');
        } else {
            return $this->sendError("API ERROR", "message");
        }
    }

    public function rejectClaimByOperator(RejectClaimByOperator $request)
    {
        $response = $this->claimService->rejectByOperator($request);

        if ($response) {
            return $this->sendSuccess('Rad Qilindi!', 'Success');
        } else {
            return $this->sendError("API ERROR", "message");
        }
    }

    public function conclusionClaimByInspector(ConclusionClaimByInspector $request)
    {
        $response = $this->claimService->conclusionByInspector($request);

        if ($response) {
            return $this->sendSuccess('Yuborildi!', 'Success');
        } else {
            return $this->sendError("API ERROR", "message");
        }
    }

    public function conclusionClaimByDirector(ConclusionClaimByDirector $request)
    {
        $response = $this->claimService->conclusionByDirector($request);

        if ($response) {
            return $this->sendSuccess('Javob berildi!', 'Success');
        } else {
            return $this->sendError("API ERROR", "message");
        }
    }

    public function rejectFromDirector(RejectFromDirector $request)
    {
        $response = $this->claimService->rejectFromDirector($request);

        if ($response) {
            return $this->sendSuccess('Qaytarildi!', 'Success');
        } else {
            return $this->sendError("API ERROR", "message");
        }
    }
    public function sendToDirector(SendToDirector $request)
    {
        $response = $this->claimService->sendToDirector($request);

        if ($response) {
            return $this->sendSuccess('Yuborildi!', 'Success');
        } else {
            return $this->sendError("API ERROR", "message");
        }
    }
}