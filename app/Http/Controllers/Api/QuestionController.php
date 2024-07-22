<?php

namespace App\Http\Controllers\Api;

use App\DTO\QuestionDto;
use App\Http\Requests\QuestionRequest;
use App\Http\Resources\LevelResource;
use App\Http\Resources\QuestionResource;
use App\Models\Level;
use App\Services\QuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;


class QuestionController extends BaseController
{

    public function __construct(
        public QuestionService $questionService,
    ){}

    public function questionUsers(): JsonResponse
    {
        try {
            return $this->sendSuccess(QuestionResource::collection($this->questionService->getQuestions()), 'Questions by user');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function sendQuestion(QuestionRequest $request): JsonResponse
    {
        try {
            $dto = new QuestionDTO();
            $dto->setObject($request->object_id)
                ->setMeta($request->questions)
                ->setLevel($request->level);

//            $this->questionService->setChecklist($dto);
            $this->questionService->createViolation($dto);

            return $this->sendSuccess([], 'Successfully send question');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function levels(): JsonResponse
    {
        try {
            if (\request('id')){
                $level = Level::findOrFail(request('id'));
                return $this->sendSuccess(LevelResource::make($level), 'Level by id');
            }
            $levels = Level::search(request('s'))
                ->when(request('sort'), function ($query) {
                    $query->orderBy('id', request('sort'));
                })->get();
            return $this->sendSuccess(LevelResource::collection($levels), 'All Levels');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function sendAnswer(): JsonResponse
    {
        try {
            $this->questionService->createActViolation(\request('violations'));
            return $this->sendSuccess([], 'Successfully send answer');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }
}
