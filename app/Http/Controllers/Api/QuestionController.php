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

//    public function createRegulation(): JsonResponse
//    {
//        DB::beginTransaction();
//        try {
//            $this->questionService->createRegulation(\request()->all());
//            DB::commit();
//            return $this->sendSuccess([], 'Successfully created regulation');
//        }catch (\Exception $exception){
//            DB::rollBack();
//            return $this->sendError($exception->getMessage(), $exception->getLine());
//        }
//    }

    public function createRegulation()
    {
        $data = \request()->all();
        try {
            $user = Auth::user();
            $roleId = (int)$user->getRoleFromToken();
            $object = Article::find($data['object_id']);

            $monitoring = $this->createMonitoring($data, $object, $roleId);

            if (!empty($data['positive']))
            {
                $this->handleChecklists($data['positive'], $object, $data['block_id'], $roleId, true);
            }
            if (!empty($data['negative'])){
                $allRoleViolations = $this->handleChecklists($data['negative'], $object, $data['block_id'], $roleId, false);
                $this->createRegulations($allRoleViolations, $object, $monitoring);
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    private function createMonitoring($data, $object, $roleId)
    {
        return Monitoring::create([
            'object_id' => $object->id,
            'number' => 123,
            'regulation_type_id' => 1,
            'block_id' => $data['block_id'],
            'created_by' => Auth::id(),
            'created_by_role' => $roleId,
        ]);
    }

    private function handleChecklists($checklists, $object, $blockId, $roleId, $isPositive)
    {
        $allRoleViolations = [];
        foreach ($checklists as $index => $checklistData) {
            $checklist = $this->getOrCreateChecklist($checklistData, $object, $blockId);

            $this->updateChecklistStatus($checklist, $checklistData, $roleId, $isPositive);

            if (!$isPositive) {
                $allRoleViolations[$index] = $this->handleViolations($checklistData, $checklist);
            }
        }
        return $allRoleViolations;
    }

    private function getOrCreateChecklist($checklistData, $object, $blockId)
    {
        return $checklistData['checklist_id']
            ? CheckListAnswer::find($checklistData['checklist_id'])
            : CheckListAnswer::create([
                'block_id' => $blockId,
                'status' => CheckListStatusEnum::NOT_FILLED->value,
                'work_type_id' => $checklistData['work_type_id'],
                'question_id' => $checklistData['question_id'],
                'floor' => $checklistData['floor'] ?? null,
                'object_id' => $object->id,
                'object_type_id' => $object->object_type_id
            ]);
    }

    private function updateChecklistStatus($checklist, $checklistData, $roleId, $isPositive)
    {
        $answeredField = $this->getAnsweredFieldByRole($roleId);
        $statusField = $isPositive ? $this->getPositiveStatusField($checklist, $roleId, $checklistData) : CheckListStatusEnum::RAISED->value;

        $checklist->update([
            $answeredField => $isPositive ? 1 : 2,
            'status' => $statusField ?? $checklist->status
        ]);
    }

    private function getAnsweredFieldByRole($roleId)
    {
        return match ($roleId) {
            UserRoleEnum::INSPECTOR->value => 'inspector_answered',
            UserRoleEnum::TEXNIK->value => 'technic_answered',
            UserRoleEnum::MUALLIF->value => 'author_answered',
            default => throw new \UnhandledMatchError("Unhandled roleId: {$roleId}"),

        };
    }

    private function getPositiveStatusField($checklist, $roleId, $checklistData)
    {
        return ($checklistData['status'] != CheckListStatusEnum::NOT_FILLED->value)
            ? match ($roleId) {
                UserRoleEnum::INSPECTOR->value => CheckListStatusEnum::CONFIRMED->value,
                UserRoleEnum::TEXNIK->value => ($checklist->author_answered == 1) ? CheckListStatusEnum::SECOND->value : null,
                UserRoleEnum::MUALLIF->value => ($checklist->technic_answered == 1) ? CheckListStatusEnum::SECOND->value : null,
                default => null
            }
            : null;
    }
    private function handleViolations($checklistData, $checklist)
    {
        $roleViolations = [];
        $question = Question::findOrFail($checklistData['question_id']);

        foreach ($checklistData['violations'] as $value) {
            $violation = $this->createViolation($value, $question, $checklist);

            $this->storeViolationImages($violation, $value['images'] ?? []);

            foreach ($value['roles'] as $role) {
                $roleViolations[$role]['violation_ids'][] = $violation->id;
                $roleViolations[$role]['deadline'] = $checklistData['deadline'];
                $roleViolations[$role]['checklist_id'] = $checklist->id;
                $roleViolations[$role]['question_id'] = $checklistData['question_id'];
            }
        }

        return $roleViolations;
    }

    private function createViolation($violationData, $question, $checklist)
    {
        return Violation::create([
            'question_id' => $question->id,
            'title' => $question->name,
            'description' => $violationData['comment'],
            'bases_id' => $violationData['basis_id'],
            'checklist_id' => $checklist->id,
            'level_id' => 1
        ]);
    }

    private function storeViolationImages($violation, $images)
    {
        foreach ($images as $image) {
            $path = $image->store('images/violation', 'public');
            $violation->images()->create(['url' => $path]);
        }
    }

    private function createRegulations($allRoleViolations, $object, $monitoring)
    {
        foreach ($allRoleViolations as $roles) {
            foreach ($roles as $roleId => $role) {
                $regulation = $this->createRegulationEntry($object, $monitoring, $role, $roleId);
                $this->linkViolationsToRegulation($regulation, $role['violation_ids']);
            }
        }
    }

    private function createRegulationEntry($object, $monitoring, $role, $roleId)
    {
        return Regulation::create([
            'object_id' => $object->id,
            'deadline' => Carbon::now()->addDays($role['deadline']),
            'level_id' => 1,
            'checklist_id' => $role['checklist_id'],
            'question_id' => $role['question_id'],
            'regulation_status_id' => 1,
            'regulation_type_id' => 1,
            'created_by_role_id' => $object->roles()->where('user_id', Auth::id())->first()->id,
            'created_by_user_id' => $object->users()->where('user_id', Auth::id())->first()->id,
            'user_id' => $object->users()->wherePivot('role_id', $roleId)->pluck('users.id')->first(),
            'monitoring_id' => $monitoring->id,
            'role_id' => $roleId,
        ]);
    }

    private function linkViolationsToRegulation($regulation, $violationIds)
    {
        foreach ($violationIds as $violationId) {
            RegulationViolation::create([
                'regulation_id' => $regulation->id,
                'violation_id' => $violationId
            ]);
        }
    }
}
