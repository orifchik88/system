<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CheckListAnswerResource;
use App\Models\CheckListAnswer;
use App\Services\CheckListAnswerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ChecklistAnswerController extends BaseController
{

    public function __construct(protected CheckListAnswerService $service)
    {
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
}
