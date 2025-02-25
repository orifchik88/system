<?php

namespace App\Http\Controllers\Api;

use App\Enums\RegulationStatusEnum;
use App\Enums\UserRoleEnum;
use App\Http\Requests\ClaimRequests\AcceptTask;
use App\Http\Requests\ClaimRequests\AttachBLockAndOrganization;
use App\Http\Requests\ClaimRequests\AttachObject;
use App\Http\Requests\ClaimRequests\ClaimSendToMinstroy;
use App\Http\Requests\ClaimRequests\ConclusionClaimByDirector;
use App\Http\Requests\ClaimRequests\ConclusionClaimByInspector;
use App\Http\Requests\ClaimRequests\ConclusionOrganization;
use App\Http\Requests\ClaimRequests\ManualConfirmDirector;
use App\Http\Requests\ClaimRequests\rejectClaimByInspector;
use App\Http\Requests\ClaimRequests\RejectClaimByOperator;
use App\Http\Requests\ClaimRequests\RejectFromDirector;
use App\Http\Requests\ClaimRequests\SendToDirector;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\Block;
use App\Models\ClaimOrganizationReview;
use App\Models\Monitoring;
use App\Models\Regulation;
use App\Services\ClaimService;
use App\Services\HistoryService;
use Illuminate\Support\Facades\Auth;

class ClaimController extends BaseController
{


    private ClaimService $claimService;
    private HistoryService $historyService;

    public function __construct(ClaimService $claimService)
    {
        $this->middleware('auth');
        parent::__construct();
        $this->claimService = $claimService;
        $this->historyService = new HistoryService('claim_histories');
    }

    public function statisticsQuantity()
    {
        $dateFrom = request()->get('date_from', null);
        $dateTo = request()->get('date_to', null);

        $roleId = Auth::user()->getRoleFromToken() ?? null;

        $regionId = match ($roleId) {
            (string)UserRoleEnum::FVB_REG_KADR->value, (string)UserRoleEnum::SEOM_REG_KADR->value, (string)UserRoleEnum::INSPEKSIYA->value, (string)UserRoleEnum::EKOLOGIYA->value,
            (string)UserRoleEnum::HUDUDIY_KUZATUVCHI->value,(string)UserRoleEnum::BUXGALTER->value,
            (string)UserRoleEnum::NOGIRONLAR_JAM->value, (string)UserRoleEnum::NOGIRONLAR_ASSOT->value, (string)UserRoleEnum::UY_JOY_INSPEKSIYA->value,
            (string)UserRoleEnum::OPERATOR->value, (string)UserRoleEnum::INSPECTOR->value => Auth::user()->region_id ?? Auth::user()->region_id ?? null,
            default => request()->get('region', null),
        };

        $districtId = match ($roleId) {
            (string)UserRoleEnum::FVB->value, (string)UserRoleEnum::SEOM->value => Auth::user()->district_id ?? Auth::user()->district_id ?? null,
            default => request()->get('district', null),
        };

        if ($roleId == (string)UserRoleEnum::INSPECTOR->value)
            $data = $this->claimService->getStatisticsForInspector();
        else
            $data = $this->claimService->getStatisticsCount(
                regionId: $regionId,
                districtId: $districtId,
                expired: null,
                dateFrom: $dateFrom,
                dateTo: $dateTo
            );

        return $this->sendSuccess($data, 'Successfully sent!');
    }

    public function organizationStatisticsQuantity()
    {
        $dateFrom = request()->get('date_from', null);
        $dateTo = request()->get('date_to', null);

        $roleId = Auth::user()->getRoleFromToken() ?? null;

        $regionId = match ($roleId) {
            (string)UserRoleEnum::FVB_REG_KADR->value, (string)UserRoleEnum::SEOM_REG_KADR->value, (string)UserRoleEnum::EKOLOGIYA->value,
            (string)UserRoleEnum::NOGIRONLAR_JAM->value, (string)UserRoleEnum::NOGIRONLAR_ASSOT->value, (string)UserRoleEnum::UY_JOY_INSPEKSIYA->value,
            => Auth::user()->region_id ?? Auth::user()->region_id ?? null,
            default => request()->get('region', null),
        };

        $districtId = match ($roleId) {
            (string)UserRoleEnum::FVB->value, (string)UserRoleEnum::SEOM->value => Auth::user()->district_id ?? Auth::user()->district_id ?? null,
            default => request()->get('district', null),
        };


        $data = $this->claimService->getOrganizationStatistics(
            roleId: $roleId,
            regionId: $regionId,
            districtId: $districtId,
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
            role_id: (in_array($roleId, [15, 16, 17, 18, 19, 3, 34])) ? $roleId : null
        );

        if (!$data) {
            return $this->sendError("Ma'lumot topilmadi!", [], 422);
        }

        return $this->sendSuccess($data, 'Success!');
    }

    public function tasksList()
    {
        $task_id = request()->get('task_id', null);
        $object_task_id = request()->get('object_task_id', null);
        $building_name = request()->get('name', null);
        $customer = request()->get('customer', null);
        $sender = request()->get('sender', null);
        $startDate = request()->get('start_date', null);
        $endDate = request()->get('end_date', null);
        $districtId = request()->get('district_id', null);
        $sortBy = request()->get('sort_by_date', 'desc');

        $status = request()->get('status', null);
        $expired = request()->get('expired', 0);

        $roleId = Auth::user()->getRoleFromToken() ?? null;

        $regionId = match ($roleId) {
            (string)UserRoleEnum::FVB_REG_KADR->value, (string)UserRoleEnum::SEOM_REG_KADR->value, (string)UserRoleEnum::INSPEKSIYA->value,
            (string)UserRoleEnum::HUDUDIY_KUZATUVCHI->value,(string)UserRoleEnum::BUXGALTER->value,
            (string)UserRoleEnum::NOGIRONLAR_JAM->value, (string)UserRoleEnum::NOGIRONLAR_ASSOT->value, (string)UserRoleEnum::UY_JOY_INSPEKSIYA->value, (string)UserRoleEnum::EKOLOGIYA->value,
            (string)UserRoleEnum::OPERATOR->value, (string)UserRoleEnum::INSPECTOR->value => Auth::user()->region_id ?? Auth::user()->region_id ?? null,
            default => request()->get('region_id', null),
        };

        $districtId = match ($roleId) {
            (string)UserRoleEnum::FVB->value, (string)UserRoleEnum::SEOM->value => Auth::user()->district_id ?? Auth::user()->district_id ?? null,
            default => request()->get('district_id', null),
        };

        $data = $this->claimService->getTaskList(
            regionId: $regionId,
            task_id: $task_id,
            object_task_id: $object_task_id,
            name: $building_name,
            customer: $customer,
            sender: $sender,
            districtId: $districtId,
            sortBy: $sortBy,
            status: $status,
            expired: $expired,
            role_id: (in_array($roleId, [15, 16, 17, 18, 19, 3, 21, 23, 20, 22, 24, 25, 34, 35])) ? $roleId : null,
            start_date: $startDate,
            end_date: $endDate,
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
        $filters = request()->only(['name']);
        $data = $this->claimService->getObjects(
            id: $id,
            filters: $filters,
            type: request()->get('type')
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

        $block = Block::find($blocks[0]);
        $article = Article::query()->where('id', $block->article_id)->first();
        if ($article->cost > 0)
            $errors[] = "Obyekt summasi to'liq to'lanmagan";

        foreach ($blocks as $blockId) {
            $block = Block::find($blockId);
            $monitorings = Monitoring::query()->where('block_id', $blockId)->get();

            foreach ($monitorings as $monitoring) {
                $reguluations = Regulation::query()->where('monitoring_id', $monitoring->id)->whereNotIn('regulation_status_id', [RegulationStatusEnum::LATE_EXECUTION, RegulationStatusEnum::ELIMINATED])->count();
                if($reguluations > 0)
                    $errors[] = "Blok ($blockId)da $reguluations ta yopilmagan yozma ko'rsatma mavjud.";
            }

            if (!$block) {
                $errors[] = "Blok ($blockId) topilmadi.";
            } elseif ($block->status) {
                $errors[] = "Blok ($blockId) qurilish ishlari yakunlanmagan.";
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
            $review = ClaimOrganizationReview::with('monitoring')->where('id', $response['review_id'])->first();
            $claim = $this->claimService->getClaimById(id: $review->monitoring->claim->id, role_id: null);

            return $this->sendSuccess($claim, 'Success');
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

    public function rejectByOperator(RejectClaimByOperator $request)
    {
        $response = $this->claimService->rejectClaimByOperator($request);

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

    public function manualAccept(ManualConfirmDirector $request)
    {
        $roleId = Auth::user()->getRoleFromToken() ?? null;
        if ($roleId != UserRoleEnum::INSPEKSIYA->value)
            return $this->sendError("Siz bu amalni bajara olmaysiz!", "message");

        $response = $this->claimService->manualConfirmByDirector($request);

        if ($response) {
            return $this->sendSuccess('Yuborildi!', 'Success');
        } else {
            return $this->sendError("Bloklar yakunlanmagan", "message");
        }
    }
}
