<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CheckListAnswerResource;
use App\Models\CheckListAnswer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ChecklistAnswerController extends BaseController
{

    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index(): JsonResponse
    {
        try {

            $objectIds = $this->user->objects()->where('role_id', $this->roleId)->pluck('article_id');

            $query = CheckListAnswer::query()->whereIn('object_id', $objectIds);
                 switch ($this->roleId) {
                     case 3:
                         $query->where('status', 3)->where('inspector_answered', null);
                         break;
                     case 6:
                         $query->where('status', 2)->whereNotIn('work_type_id',  [1, 11])->whereNull('technic_answered');
                         break;
                     case 7:
                         $query->where('status', 2)->whereNotIn('work_type_id',  [1, 11])->whereNull('author_answered');
                         break;
                     default:
                         break;
                 }

                 $checkListAnswers = $query->paginate(request('per_page', 10));

                 return $this->sendSuccess(CheckListAnswerResource::collection($checkListAnswers), 'All Checklist Answers retrieved successfully.', pagination($checkListAnswers));

        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage());
        }
    }
}
