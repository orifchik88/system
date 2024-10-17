<?php

namespace App\Http\Controllers\Api;

use App\DTO\QuestionDto;
use App\Enums\CheckListStatusEnum;
use App\Enums\UserRoleEnum;
use App\Http\Requests\QuestionRequest;
use App\Http\Resources\LevelResource;
use App\Http\Resources\QuestionResource;
use App\Models\Article;
use App\Models\CheckList;
use App\Models\CheckListAnswer;
use App\Models\Level;
use App\Models\Monitoring;
use App\Models\Question;
use App\Models\Regulation;
use App\Models\RegulationViolation;
use App\Models\Violation;
use App\Services\QuestionService;
use Carbon\Carbon;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;


class QuestionController extends BaseController
{

    public function __construct(
        public QuestionService $questionService,
    )
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function questionUsers(): JsonResponse
    {
        try {
            return $this->sendSuccess(QuestionResource::collection($this->questionService->getQuestions()), 'Questions by user');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function levels(): JsonResponse
    {
        try {
            if (\request('id')) {
                $level = Level::findOrFail(request('id'));
                return $this->sendSuccess(LevelResource::make($level), 'Level by id');
            }
            $levels = Level::search(request('s'))
                ->when(request('sort'), function ($query) {
                    $query->orderBy('id', request('sort'));
                })->get();
            return $this->sendSuccess(LevelResource::collection($levels), 'All Levels');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function sendAnswer(): JsonResponse
    {
        try {
            $dto = new QuestionDTO();
            $dto->setRegulationId(request('regulation_id'))
                ->setMeta(request('violations'));
            $this->questionService->createActViolation($dto);
            return $this->sendSuccess([], 'Successfully send answer');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function createRegulation(): JsonResponse
    {
        DB::beginTransaction();
        try {
            $this->questionService->createRegulation(\request()->all());
            DB::commit();
            return $this->sendSuccess([], 'Successfully created regulation');
        }catch (\Exception $exception){
            DB::rollBack();
            return $this->sendError($exception->getMessage(), $exception->getLine());
        }
    }

}
