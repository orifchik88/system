<?php

namespace App\Services;

use App\DTO\QuestionDto;
use App\Exceptions\NotFoundException;
use App\Models\Article;
use App\Models\Monitoring;
use App\Models\Question;
use App\Models\Regulation;
use App\Models\RegulationViolation;
use App\Models\Violation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuestionService
{

    protected $user;
    public QuestionDto $dto;

    public function __construct(
        protected  Question $questions,
    ){
        $this->user = Auth::guard('api')->user();
    }

    public function getQuestions()
    {
        if (!request('level') || !request('object_type_id')){
            throw new NotFoundException('level and object_type_id are required.');
        }
        return $this->user->roles->first()->questions->where('level_id', request('level'))->where('object_type_id', request('object_type_id'));
    }

    public function createViolation($dto)
    {
        DB::beginTransaction();
        try {
            $object = Article::find($dto->objectId);

            $monitoring = new Monitoring();
            $monitoring->object_id = $dto->objectId;
            $monitoring->number = 123;
            $monitoring->regulation_type_id = 1;
            $monitoring->created_by = $this->user->id;
            $monitoring->save();


            $violations = [];
            foreach ($dto->meta as $data) {
                $question = Question::find($data['id']);
                $violation = Violation::create([
                    'question_id' => $question->id,
                    'images' => $data['images'],
                    'title' => $question->question,
                    'description' => $question->answer,
                    'level_id' => $dto->levelId,
                ]);
                $violations[] = [
                    'violation_id' => $violation->id,
                    'roles' => $data['roles']
                ];
            }


            $roles = [];
            foreach ($dto->meta as $question) {
                $roles = array_merge($roles, $question['roles']);
            }
            $roles = array_unique($roles);

            foreach ($roles as $role) {
                $regulation = Regulation::create([
                    'object_id' => $dto->objectId,
                    'regulation_number' => '123',
                    'regulation_number_id' => 1,
                    'level_id' => $dto->levelId,
                    'regulation_status_id' =>1,
                    'regulation_type_id' =>1,
                    'created_by_role_id' =>$object->roles()->where('user_id', \auth()->id())->first()->id,
                    'created_by_user_id' =>$object->users()->where('user_id', \auth()->id())->first()->id,
                    'user_id' =>$object->users()->wherePivot('role_id', $role)->pluck('users.id')->first(),
                    'monitoring_id' =>$monitoring->id,
                    'role_id' =>$role,
                    'act_status_id' =>1,
                    'deadline' => Carbon::now(),
                ]);

                // regulation_violation jadvaliga yozish
                foreach ($violations as $v) {
                    if (in_array($role, $v['roles'])) {
                        RegulationViolation::create([
                            'regulation_id' => $regulation->id,
                            'violation_id' => $v['violation_id'],
                            'user_id' => $object->users()->wherePivot('role_id', $role)->pluck('users.id')->first()
                        ]);
                    }
                }
            }
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
            dd($exception->getMessage());
        }


    }


    public function setChecklist($dto): void
    {
        DB::beginTransaction();
        try {
            $monitoring = new Monitoring();
            $monitoring->object_id = $dto->objectId;
            $monitoring->number = 123;
            $monitoring->regulation_type_id = 1;
            $monitoring->created_by = $this->user->id;
            $monitoring->save();

            $roleQuestions = [];
            $data = array_filter($dto->meta, function($question) {
                return !$question['status'];
            });

            $object = Article::find($dto->objectId);

            foreach ($data as $item) {
                foreach ($item['roles'] as $roleId) {
                    if (!isset($roleQuestions[$roleId])) {
                        $roleQuestions[$roleId] = [];
                    }
                    $roleQuestions[$roleId][] = [
                        'question_id' => $item['id'],
                        'images' => $item['images'],
                        'blocks' => $item['blocks'] ?? []
                    ];
                }
            }

            foreach ($roleQuestions as $key =>$roleQuestion) {
                $regulation = new Regulation();
                $regulation->object_id = $dto->objectId;
                $regulation->regulation_number = 123;
                $regulation->deadline = Carbon::now();
                $regulation->regulation_status_id = 1;
                $regulation->regulation_type_id = 1;
                $regulation->monitoring_id = $monitoring->id;
                $regulation->act_status_id = 1;
                $regulation->level_id = $dto->levelId;
                $regulation->user_id  = $object->users()->wherePivot('role_id', $key)->pluck('users.id')->first();
                $regulation->role_id = $key;
                $regulation->regulation_number_id = 1;
                $regulation->created_by_role_id = $object->roles()->where('user_id', \auth()->id())->first()->id;
                $regulation->created_by_user_id = $object->users()->where('user_id', \auth()->id())->first()->id;
                $regulation->save();
            }


            DB::commit();

        }catch (\Exception $exception){
            DB::rollBack();
            dd($exception->getMessage(), $exception->getLine());
        }
    }
}
