<?php

namespace App\Services;

use App\DTO\QuestionDto;
use App\Enums\BlockModeEnum;
use App\Enums\CheckListStatusEnum;
use App\Enums\ObjectTypeEnum;
use App\Enums\UserRoleEnum;
use App\Enums\WorkTypeStatusEnum;
use App\Exceptions\NotFoundException;
use App\Models\ActViolation;
use App\Models\ActViolationBlock;
use App\Models\Article;
use App\Models\Block;
use App\Models\BlockViolation;
use App\Models\CheckListAnswer;
use App\Models\Monitoring;
use App\Models\Question;
use App\Models\Regulation;
use App\Models\RegulationDemand;
use App\Models\RegulationViolation;
use App\Models\RegulationViolationBlock;
use App\Models\Violation;
use App\Models\WorkType;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuestionService
{

    protected $user;
    public QuestionDto $dto;

    public function __construct(
        protected Question $questions,
    )
    {
        $this->user = Auth::guard('api')->user();
    }

    public function getQuestionList($blockId = null, $type = null)
    {
        $block = Block::find($blockId) ?? null;


        $workTypes = WorkType::query()->orderBy('id', 'ASC')->get();

        $generatedQuestions = [];

        $answers = CheckListAnswer::where('block_id', $blockId)
            ->get()
            ->keyBy(function ($answer) {
                return $answer->work_type_id . '-' . $answer->question_id . '-' . $answer->floor;
            });

        foreach ($workTypes as $workType) {
            $questions = Question::query()
                ->when($type, function ($query) use ($type) {
                    $query->where('type', $type);
                })->when($block, function ($query) use ($block) {
                    if ($block->block_mode_id == BlockModeEnum::TARMOQ) {
                        $query->where('object_type_id', ObjectTypeEnum::LINEAR);
                    } else {
                        $query->where('object_type_id', ObjectTypeEnum::BUILDING);
                    }
                })
                ->where('work_type_id', $workType->id)->get();

            $questionArray = [];
            if ($workType->is_multiple_floor && isset($block) && $block->floor != null) {
                for ($floor = 1; $floor <= $block->floor; $floor++) {
                    $workTypeStatus = $this->getStatusOfWorkType($this->filterAnswers($answers, $workType->id, floor: $floor));
                    foreach ($questions as $question) {
                        $answerKey = $workType->id . '-' . $question->id . '-' . $floor;
                        $answer = $answers->get($answerKey);

                        $questionArray[] = [
                            'question' => $question->name,
                            'question_id' => $question->id,
                            'type' => $question->type,
                            'floor' => $floor ?? null,
                            'work_type_status' => $workTypeStatus,
                            'status' => $answer ? $answer->status : 1,
                            'checklist_id' => $answer ? $answer->id : null,
                            'inspector_answered' => $answer ? $answer->inspector_answered: null,
                            'technic_answered' => $answer ? $answer->technic_answered : null,
                            'author_answered' => $answer ? $answer->author_answered : null,
                        ];
                    }
                }
            } else {
                $workTypeStatus = $this->getStatusOfWorkType($this->filterAnswers($answers, $workType->id));
                foreach ($questions as $question) {
                    $answerKey = $workType->id . '-' . $question->id . '-';
                    $answer = $answers->get($answerKey);

                    $questionArray[] = [
                        'question' => $question->name,
                        'question_id' => $question->id,
                        'type' => $question->type,
                        'floor' => null,
                        'status' => $answer ? $answer->status : 1,
                        'work_type_status' => $workTypeStatus,
                        'checklist_id' => $answer ? $answer->id : null,
                        'inspector_answered' => $answer ? $answer->inspector_answered: null,
                        'technic_answered' => $answer ? $answer->technic_answered : null,
                        'author_answered' => $answer ? $answer->author_answered : null,
                    ];
                }
            }
            if (!empty($questionArray))
                $generatedQuestions[] = [
                    "work_type_id" => $workType->id,
                    "name" => $workType->name,
                    "questions" => $questionArray
                ];
        }

        return $generatedQuestions;
    }

    private function getStatusOfWorkType($answers): WorkTypeStatusEnum
    {
        $workTypeStatus = WorkTypeStatusEnum::NOT_STARTED;
        $countConfirmedQuestions = 0;

        if ($answers->count() > 0) {
            foreach ($answers as $item) {
                if ($item->status == 5)
                    $countConfirmedQuestions++;
            }
            if ($countConfirmedQuestions == $answers->count())
                $workTypeStatus = WorkTypeStatusEnum::CONFIRMED;
            else
                $workTypeStatus = WorkTypeStatusEnum::IN_PROCESS;
        }


        return $workTypeStatus;
    }

    private function filterAnswers($answers, $workTypeId, $floor = null, $questionId = null) {
        return $answers->filter(function ($value, $key) use ($workTypeId, $floor, $questionId) {
            $parts = explode('-', $key);

            if ($parts[0] != $workTypeId) {
                return false;
            }

            if (!is_null($questionId) && isset($parts[1]) && $parts[1] != $questionId) {
                return false;
            }

            if (!is_null($floor) && isset($parts[2]) && $parts[2] != $floor) {
                return false;
            }

            return true;
        });
    }

    public function getQuestions()
    {
        if (!request('level') || !request('object_type_id')) {
            throw new NotFoundException('level and object_type_id are required.');
        }

        $role = $this->user->roles->first();

        $questions = $this->questions
            ->where('level_id', request('level'))
            ->where('author_id', $role->id)
            ->where('object_type_id', request('object_type_id'))->get();

        if (!$questions)
            throw new NotFoundException('User has no roles or role has no questions.');

        return $questions;
    }




    public function createRegulation($data)
    {
        try {
            $user = Auth::user();
            $roleId = (int)$user->getRoleFromToken();
            $object = Article::find($data['object_id']);

            $monitoring = $this->createMonitoring($data, $object, $roleId);

            if (!empty($data['public'])){
                $this->createPublicChecklist($data['public'], $object, $roleId, $monitoring->id);
            }

            if (!empty($data['positive']))
            {
                $this->handleChecklists($data['positive'], $object, $data['block_id'], $roleId, true, null);
            }
            if (!empty($data['negative'])){
                $allRoleViolations = $this->handleChecklists($data['negative'], $object, $data['block_id'], $roleId, false, null);
                $this->createRegulations($allRoleViolations, $object, $monitoring->id, $roleId);
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

    }

    private function createPublicChecklist($data, $object, $roleId, $monitoringID)
    {
            if (!empty($data['positive'])){
                $this->handleChecklists($data['positive'], $object, null, $roleId, true, $monitoringID);
            }
            if (!empty($data['negative'])){
                $allRoleViolations = $this->handleChecklists($data['negative'], $object, null, $roleId, false, $monitoringID);
                $this->createRegulations($allRoleViolations, $object, $monitoringID, $roleId);
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

    private function handleChecklists($checklists, $object, $blockId, $roleId, $isPositive, $monitoringID)
    {
        $allRoleViolations = [];
        foreach ($checklists as $index => $checklistData) {
            $checklist = $this->getOrCreateChecklist($checklistData, $object, $blockId, $monitoringID);

            $this->updateChecklistStatus($checklist, $checklistData, $roleId, $isPositive);

            if (!$isPositive) {
                $allRoleViolations[$index] = $this->handleViolations($checklistData, $checklist);
            }
        }
        return $allRoleViolations;
    }

    private function getOrCreateChecklist($checklistData, $object, $blockId, $monitoringID)
    {
        return isset($checklistData['checklist_id'])
            ? CheckListAnswer::find($checklistData['checklist_id'])
            : CheckListAnswer::create([
                'block_id' => $blockId,
                'status' => $checklistData['status'] ?? CheckListStatusEnum::NOT_FILLED->value,
                'work_type_id' => $checklistData['work_type_id'],
                'question_id' => $checklistData['question_id'],
                'monitoring_id' => $monitoringID,
                'floor' => $checklistData['floor'] ?? null,
                'object_id' => $object->id,
                'object_type_id' => $object->object_type_id
            ]);
    }

    private function updateChecklistStatus($checklist, $checklistData, $roleId, $isPositive)
    {
        $answeredField = $this->getAnsweredFieldByRole($roleId);
        $statusField = $isPositive
            ? $this->getPositiveStatusField($checklist, $roleId, $checklistData)
            : ($checklist->status == CheckListStatusEnum::NOT_FILLED ? 1 : CheckListStatusEnum::RAISED->value);
        if ($statusField === 'inspector_answered' && $isPositive === false) {
            $updateData = [
                $answeredField => 1,
                'status' => $statusField ?? $checklist->status,
                'technic_answered' => null,
                'author_answered' => null,
            ];
        } elseif ($statusField === 'technic_answered' && $isPositive === false) {
            $updateData = [
                $answeredField => 2,
                'status' => $statusField ?? $checklist->status,
                'inspector_answered' => null,
                'author_answered' => null,
            ];
        } elseif ($statusField === 'author_answered' && $isPositive === false) {
            $updateData = [
                $answeredField => 2,
                'status' => $statusField ?? $checklist->status,
                'inspector_answered' => null,
                'technic_answered' => null,
            ];
        } else {
            $updateData = [
                $answeredField => $isPositive ? 1 : 2,
                'status' => $statusField ?? $checklist->status,
            ];
        }

        $checklist->update($updateData);
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

    private function createRegulations($allRoleViolations, $object, $monitoringID, $createdByRole)
    {
        foreach ($allRoleViolations as $roles) {
            foreach ($roles as $roleId => $role) {
                $regulation = $this->createRegulationEntry($object, $monitoringID, $role, $roleId, $createdByRole);
                $this->linkViolationsToRegulation($regulation, $role['violation_ids']);
            }
        }
    }

    private function createRegulationEntry($object, $monitoringID, $role, $roleId, $createdByRole)
    {
        return Regulation::create([
            'object_id' => $object->id,
            'deadline' => Carbon::now()->addDays($role['deadline']),
            'checklist_id' => $role['checklist_id'],
            'question_id' => $role['question_id'],
            'regulation_status_id' => 1,
            'level_id' => 1,
            'regulation_type_id' => 1,
            'created_by_role_id' => $createdByRole,
            'created_by_user_id' => Auth::id(),
            'user_id' => $object->users()->wherePivot('role_id', $roleId)->pluck('users.id')->first(),
            'monitoring_id' => $monitoringID,
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


    public function createActViolation($dto)
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $roleId = $user->getRoleFromToken();
            $regulation = Regulation::query()->findOrFail($dto->regulationId);

//            $hasStatusOne = $regulation->actViolations->contains(function ($actViolation) {
//                return $actViolation->status == 1;
//            });

//            if ($hasStatusOne) {
//                throw new NotFoundException('Faol chora tadbir mavjud');
//            }
//
            $regulation->update([
                'regulation_status_id' => 2,
            ]);

            foreach ($dto->meta as $item) {
                $act = ActViolation::create([
                    'regulation_violation_id' => $item['violation_id'],
                    'regulation_id' => $dto->regulationId,
                    'user_id' => Auth::id(),
                    'act_status_id' => 1,
                    'comment' => $item['comment'],
                    'role_id' => $roleId,
                    'act_violation_type_id' => 1,
                    'status' => ActViolation::PROGRESS,
                ]);

                $demands = RegulationDemand::create([
                    'regulation_violation_id' => $dto->regulationId,
                    'user_id' => Auth::id(),
                    'role_id' => $roleId,
                    'act_status_id' => 1,
                    'act_violation_type_id' => 1,
                    'comment' => $item['comment'],
                    'act_violation_id' => $act->id,
                    'status' => ActViolation::PROGRESS
                ]);

                if (!empty($item['images']))
                {
                    foreach ($item['images'] as $image) {
                        $path = $image->store('images/act-violation', 'public');
                        $act->images()->create(['url' => $path]);
                        $demands->images()->create(['url' => $path]);
                    }
                }

                if (!empty($item['files']))
                {
                    foreach ($item['files'] as $document) {
                        $path = $document->store('document/act-violation', 'public');
                        $act->documents()->create(['url' => $path]);
                        $demands->documents()->create(['url' => $path]);
                    }
                }

            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

}
