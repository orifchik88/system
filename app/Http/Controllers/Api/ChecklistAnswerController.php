<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CheckListAnswerResource;
use App\Models\CheckListAnswer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ChecklistAnswerController extends BaseController
{
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user();
            $roleId = $user->getRoleFromToken();

            $objectIds = $user->objects()->pluck('id');

            $query = CheckListAnswer::query()->whereIn('object_id', $objectIds);
                 switch ($roleId) {
                     case 3:
                         $query->where('status', 3)->where('inspector_answered', null);
                         break;
                     case 6:
                         $query->where('status', 2)->where('technic_answered', null);
                         break;
                     case 7:
                         $query->where('status', 2)->where('author_answered', null);
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
