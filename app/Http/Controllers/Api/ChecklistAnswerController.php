<?php

namespace App\Http\Controllers\Api;

use App\Enums\LogType;
use App\Http\Resources\CheckListAnswerResource;
use App\Models\CheckList;
use App\Models\CheckListAnswer;
use App\Services\CheckListAnswerService;
use App\Services\HistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ChecklistAnswerController extends BaseController
{

    private HistoryService $historyService;
    public function __construct(protected CheckListAnswerService $service)
    {
        $this->historyService = new HistoryService('check_list_histories');

        $this->middleware('auth');
        parent::__construct();
    }

    public function index(): JsonResponse
    {
        try {
            $query = $this->service->getChecklists($this->user, $this->roleId, 1);
            $checkListAnswers = $query->paginate(request('per_page', 10));

            return $this->sendSuccess(CheckListAnswerResource::collection($checkListAnswers), 'All Checklist Answers retrieved successfully.', pagination($checkListAnswers));

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage());
        }
    }

    public function checklistStatusChange(): JsonResponse
    {
        try {
            $data = request('regular_checklist');
            foreach ($data as $item) {
                $checklist = CheckListAnswer::findOrFail($item['checklist_id']);
                $checklist->update([
                    'status' => $item['status']
                ]);

                if ($item['status'] == 3)
                {
                    $status = 2;
                }else{
                    $status = 1;
                }

                $meta = ['user_answered' => $status, 'user_id' => $this->user->id, 'role_id' => $this->roleId];

                $this->historyService->createHistory(
                    guId: $checklist->id,
                    status: $checklist->status->value,
                    type: LogType::TASK_HISTORY,
                    date: null,
                    comment: $item['comment'] ?? "",
                    additionalInfo: $meta
                );
            }

            return $this->sendSuccess([], 'Checklist status updated successfully.');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage());
        }
    }
}
