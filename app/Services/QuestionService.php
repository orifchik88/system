<?php

namespace App\Services;

use App\DTO\QuestionDto;
use App\Enums\BlockModeEnum;
use App\Enums\ObjectTypeEnum;
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

        $workTypes = WorkType::all();

        $generatedQuestions = [];

        $answers = CheckListAnswer::where('block_id', $blockId)
            ->get()
            ->keyBy(function ($answer) {
                return $answer->work_type_id . '-' . $answer->question_id . '-' . $answer->floor;
            });

        foreach ($workTypes as $key => $workType) {
                $questions = Question::query()
                    ->when($type, function ($query) use ($type) {
                        $query->where('type', $type);
                    })->when($blockId, function ($query) use ($block) {
                        if ($block->block_mode_id == BlockModeEnum::TARMOQ){
                            $query->where('object_type_id', ObjectTypeEnum::LINEAR);
                        }else{
                            $query->where('object_type_id', ObjectTypeEnum::BUILDING);
                        }
                    })
                ->where('work_type_id', $workType->id)->get();

            $questionArray = [];
            if ($workType->is_multiple_floor && isset($block)) {
                for ($floor = 1; $floor <= $block->floor; $floor++) {
                    foreach ($questions as $question) {
                        $answerKey = $workType->id . '-' . $question->id . '-' . $floor;
                        $answer = $answers->get($answerKey);

                        $questionArray[] = [
                            'question' => $question->name,
                            'question_id' => $question->id,
                            'type' => $question->type,
                            'floor' => $floor ?? null,
                            'status' => $answer ? $answer->status : null,
                            'checklist_id' => $answer ? $answer->id : null,
                        ];
                    }
                }
            } else {
                foreach ($questions as $question) {
                    $answerKey = $workType->id . '-' . $question->id . '-';
                    $answer = $answers->get($answerKey);

                    $questionArray[] = [
                        'question' => $question->name,
                        'question_id' => $question->id,
                        'type' => $question->type,
                        'floor' => null,
                        'answer' => $answer ? $answer->status : false,
                        'checklist_id' => $answer ? $answer->id : null,
                    ];
                }
            }
            if (!empty($questionArray))
                $generatedQuestions[] = [
                    "name" => $workType->name,
                    "questions" => $questionArray
                ];
        }

        return $generatedQuestions;
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

    public function createViolation($dto)
    {
        DB::beginTransaction();
        try {
            $roles = [];

            $object = Article::find($dto->objectId);
            $monitoring = new Monitoring();
            $monitoring->object_id = $dto->objectId;
            $monitoring->number = 123;
            $monitoring->regulation_type_id = 1;
            $monitoring->created_by = $this->user->id;
            $monitoring->save();

            $violations = [];
            foreach ($dto->meta as $data) {
                $question = Question::query()->find($data['question_id']);
                $violation = Violation::query()->create([
                    'question_id' => $question->id,
                    'title' => $question->question,
                    'description' => $question->answer,
                    'level_id' => $dto->levelId,
                ]);

                foreach ($data['violations'] as $item) {
                    $roles = array_merge($roles, $item['roles']);
                }

                $violations[] = [
                    'violation_id' => $violation->id,
                    'violations' => $data['violations'],
                ];
            }

            $uniqueRoles = array_unique($roles);


            foreach ($uniqueRoles as $role) {

                $deadline = collect($dto->deadline)->firstWhere('role_id', $role);
                $regulation = Regulation::create([
                    'object_id' => $dto->objectId,
//                    'regulation_number' => '123',
                    'regulation_number_id' => 1,
                    'deadline' => $deadline['deadline'],
                    'level_id' => $dto->levelId,
                    'regulation_status_id' => 1,
                    'regulation_type_id' => 1,
                    'created_by_role_id' => $object->roles()->where('user_id', \auth()->id())->first()->id,
                    'created_by_user_id' => $object->users()->where('user_id', \auth()->id())->first()->id,
                    'user_id' => $object->users()->wherePivot('role_id', $role)->pluck('users.id')->first(),
                    'monitoring_id' => $monitoring->id,
                    'role_id' => $role,
//                    'act_status_id' => 1,
                ]);
                foreach ($violations as $violationData) {
                    foreach ($violationData['violations'] as $item) {

                        if (in_array($role, $item['roles'])) {
                            $regulationBlock = RegulationViolationBlock::create([
                                'regulation_id' => $regulation->id,
                                'violation_id' => $violationData['violation_id'],
                                'block_id' => $item['block_id'],
                            ]);

                            foreach ($item['images'] as $image) {
                                $imagePath = $image->store('block', 'public');
                                $regulationBlock->images()->create([
                                    'url' => $imagePath,
                                ]);
                            }
                        }
                    }
                }


            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }



//    public function createViolation($dto)
//    {
//        DB::beginTransaction();
//        try {
//            $object = Article::find($dto->objectId);
//
//            $monitoring = new Monitoring();
//            $monitoring->object_id = $dto->objectId;
//            $monitoring->number = 123;
//            $monitoring->regulation_type_id = 1;
//            $monitoring->created_by = $this->user->id;
//            $monitoring->save();
//
//
//            $violations = [];
//            foreach ($dto->meta as $data) {
//                $question = Question::find($data['id']);
//                $violation = Violation::create([
//                    'question_id' => $question->id,
//                    'title' => $question->question,
//                    'description' => $question->answer,
//                    'level_id' => $dto->levelId,
//                ]);
//
//                if (isset($data['images'])) {
//                    foreach ($data['images'] as $image) {
//                        $imagePath = $image->store('violations', 'public');
//                        $violation->imageFiles()->create([
//                            'url' => $imagePath,
//                        ]);
//                    }
//                }
//
//                $violation->blockViolations()->attach($data['blocks']);
//                $violations[] = [
//                    'violation_id' => $violation->id,
//                    'roles' => $data['roles']
//                ];
//            }
//
//
//            $roles = [];
//            foreach ($dto->meta as $question) {
//                $roles = array_merge($roles, $question['roles']);
//            }
//            $roles = array_unique($roles);
//
//            foreach ($roles as $role) {
//                $regulation = Regulation::create([
//                    'object_id' => $dto->objectId,
//                    'regulation_number' => '123',
//                    'regulation_number_id' => 1,
//                    'level_id' => $dto->levelId,
//                    'regulation_status_id' =>1,
//                    'regulation_type_id' =>1,
//                    'created_by_role_id' =>$object->roles()->where('user_id', \auth()->id())->first()->id,
//                    'created_by_user_id' =>$object->users()->where('user_id', \auth()->id())->first()->id,
//                    'user_id' =>$object->users()->wherePivot('role_id', $role)->pluck('users.id')->first(),
//                    'monitoring_id' =>$monitoring->id,
//                    'role_id' =>$role,
//                    'act_status_id' =>1,
//                    'deadline' => Carbon::now(),
//                ]);
//
//                // regulation_violation jadvaliga yozish
//                foreach ($violations as $v) {
//                    if (in_array($role, $v['roles'])) {
//                        RegulationViolation::create([
//                            'regulation_id' => $regulation->id,
//                            'violation_id' => $v['violation_id'],
//                            'user_id' => $object->users()->wherePivot('role_id', $role)->pluck('users.id')->first()
//                        ]);
//                    }
//                }
//            }
//            DB::commit();
//        }catch (\Exception $exception){
//            DB::rollBack();
//            throw $exception;
//        }
//
//
//    }

    public function createActViolation($dto)
    {
        DB::beginTransaction();
        try {
            $regulation = Regulation::find($dto->regulationId);

            $hasStatusOne = $regulation->actViolations->contains(function ($actViolation) {
                return $actViolation->status == 1;
            });

            if ($hasStatusOne) {
                throw new NotFoundException('Faol chora tadbir mavjud');
            }

            $regulation->update([
                'regulation_status_id' => 2,
                'act_status_id' => 1,
            ]);

            foreach ($dto->meta as $item) {
                $act = ActViolation::create([
                    'violation_id' => $item['violation_id'],
                    'regulation_id' => $dto->regulationId,
                    'user_id' => Auth::id(),
                    'question_id' => $item['question_id'],
                    'act_violation_type_id' => 1,
                    'status' => ActViolation::PROGRESS,
                ]);


                foreach ($item['blocks'] as $block) {
                    $actViolationBlock = ActViolationBlock::create([
                        'act_violation_id' => $act->id,
                        'block_id' => $block['block_id'],
                        'comment' => $block['comment'],
                    ]);

                    if (isset($block['files'])) {
                        foreach ($block['files'] as $file) {
                            $filePath = $file->store('act_violation_block', 'public');
                            $actViolationBlock->documents()->create([
                                'url' => $filePath,
                            ]);
                        }
                    }
                    if (isset($block['images'])) {
                        foreach ($block['images'] as $image) {
                            $imagePath = $image->store('act_violation_block', 'public');
                            $actViolationBlock->images()->create([
                                'url' => $imagePath,
                            ]);
                        }
                    }
                }

                $demands = RegulationDemand::create([
                    'regulation_id' => $dto->regulationId,
                    'user_id' => Auth::id(),
                    'act_status_id' => 1,
                    'act_violation_type_id' => 1,
                    'comment' => 'asdfasdf',
                    'act_violation_id' => $act->id,
                    'status' => ActViolation::PROGRESS
                ]);

//            if (isset($item['files'])) {
//                foreach ($item['files'] as $file) {
//                    $filePath = $file->store('act_violation', 'public');
//                    $act->documents()->create([
//                        'url' => $filePath,
//                    ]);
//
//                    $demands->documents()->create([
//                        'url' => $filePath,
//                    ]);
//                }
//            }
//            if (isset($item['images'])) {
//                foreach ($item['images'] as $image) {
//                    $imagePath = $image->store('violations_images', 'public');
//                    $act->imagesFiles()->create([
//                        'url' => $imagePath,
//                    ]);
//                    $demands->imagesFiles()->create([
//                        'url' => $imagePath,
//                    ]);
//                }
//            }
            }

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    private
    function saveImages()
    {

    }
}
