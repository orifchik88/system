<?php

namespace App\Http\Controllers\Api;

use App\DTO\QuestionDto;
use App\Http\Requests\QuestionRequest;
use App\Http\Resources\LevelResource;
use App\Http\Resources\QuestionResource;
use App\Models\Article;
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
    }

    public function questionUsers(): JsonResponse
    {
        try {
            return $this->sendSuccess(QuestionResource::collection($this->questionService->getQuestions()), 'Questions by user');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function sendQuestion(/*QuestionRequest $request*/): JsonResponse
    {
        try {
            $dto = new QuestionDTO();
            $dto->setObject(\request('object_id'))
                ->setMeta(request('questions'))
                ->setDeadline(request('deadlines'))
                ->setLevel(request('level'));

            $this->questionService->createViolation($dto);

            return $this->sendSuccess([], 'Successfully send question');
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

    public function createRegulation()
    {
        DB::beginTransaction();
        try {
            $request = request()->all();
            $object = Article::find($request['object_id']);

            $monitoring = new Monitoring();
            $monitoring->object_id = $object->id;
            $monitoring->number = 123;
            $monitoring->regulation_type_id = 1;
            $monitoring->block_id = $request['block_id'];
            $monitoring->created_by = Auth::id();
            $monitoring->save();

            foreach ($request['negative'] as $index => $negative) {
                $roleViolations = [];
                $question = Question::query()->findOrFail($negative['question_id']);
                foreach ($negative['violations'] as $value) {
                    $violation = Violation::query()->create([
                        'question_id' => $question->id,
                        'title' => $question->name,
                        'description' => $value['comment'],
                        'bases_id' => $value['basis_id'],
                        'level_id' => 1,
                    ]);

                    if (!empty($value['images'])){
                        foreach ($value['images'] as $image) {
                            $path = $image->store('images/violation', 'public');
                            $violation->images()->create(['url' => $path]);
                        }
                    }

                    foreach ($value['roles'] as $role) {
                        if (!isset($roleViolations[$role])) {
                            $roleViolations[$role] = [];
                        }

                        $roleViolations[$role]['violation_ids'][] = $violation->id;
                        $roleViolations[$role]['deadline'] = $negative['deadline'];

                    }
                    $allRoleViolations[$index] = $roleViolations;
                }
            }


            foreach ($allRoleViolations as $roles) {
                foreach ($roles as $key => $role) {
                    $regulation = Regulation::create([
                        'object_id' => $object->id,
                        'deadline' => Carbon::now()->addDays($role['deadline']),
                        'level_id' => 1,
                        'regulation_status_id' => 1,
                        'regulation_type_id' => 1,
                        'created_by_role_id' => $object->roles()->where('user_id', \auth()->id())->first()->id,
                        'created_by_user_id' => $object->users()->where('user_id', \auth()->id())->first()->id,
                        'user_id' => $object->users()->wherePivot('role_id', $key)->pluck('users.id')->first(),
                        'monitoring_id' => $monitoring->id,
                        'role_id' => $key,
                    ]);


                    foreach ($role['violation_ids'] as $violationId) {
                        RegulationViolation::create([
                            'regulation_id' => $regulation->id,
                            'violation_id' => $violationId
                        ]);
                    }
                }
            }

            DB::commit();
            return 'success';
        }catch (\Exception $exception){
            DB::rollBack();
            return $this->sendError($exception->getMessage(), $exception->getLine());
        }
    }
}
