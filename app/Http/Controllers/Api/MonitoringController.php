<?php

namespace App\Http\Controllers\Api;

use App\Enums\CheckListStatusEnum;
use App\Enums\LogType;
use App\Enums\QuestionTypeEnum;
use App\Enums\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\MonitoringRequest;
use App\Http\Resources\CheckListAnswerFilesResource;
use App\Http\Resources\CheckListAnswerResource;
use App\Http\Resources\LevelResource;
use App\Http\Resources\MonitoringResource;
use App\Models\Article;
use App\Models\Block;
use App\Models\CheckList;
use App\Models\CheckListAnswer;
use App\Models\CheckListHistory;
use App\Models\Level;
use App\Models\Monitoring;
use App\Models\User;
use App\Services\HistoryService;
use App\Services\MessageTemplate;
use App\Services\QuestionService;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MonitoringController extends BaseController
{
    private QuestionService $questionService;
    private HistoryService $historyService;

    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
        $this->historyService = new HistoryService('check_list_histories');
    }

    public function monitoring(): JsonResponse
    {

        $user = Auth::user();
        $objectIds = $user->objects()->pluck('articles.id')->toArray();
        $monitorings = Monitoring::query()->whereIn('object_id', $objectIds)
            ->when(request('object_name'), function ($q) {
                $q->whereHas('article', function ($query) {
                    $query->where('name', 'like', '%' . request('object_name') . '%');
                });
            })
            ->when(request('region_id'), function ($q) {
                $q->whereHas('article', function ($query) {
                    $query->where('region_id', request('region_id'));
                });
            })
            ->when(request('district_id'), function ($q) {
                $q->whereHas('article', function ($query) {
                    $query->where('district_id', request('district_id'));
                });
            })
            ->when(request('funding_source'), function ($q) {
                $q->whereHas('article', function ($query) {
                    $query->where('funding_source_id', request('funding_source'));
                });
            })
            ->when(request('category'), function ($q) {
                $q->whereHas('article', function ($query) {
                    $query->where('difficulty_category_id', request('category'));
                });
            })
            ->paginate(\request('per_page', 10));

        return $this->sendSuccess(MonitoringResource::collection($monitorings), 'Monitorings', pagination($monitorings));
    }

    public function create(MonitoringRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $monitoring = new Monitoring();
            $monitoring->object_id = $request->object_id;
            $monitoring->regulation_type_id = 1;
            $monitoring->number = 1234;
            $monitoring->created_by = Auth::id();
            $monitoring->save();


            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->store('monitoring', 'public');
                    $monitoring->images()->create([
                        'url' => $imagePath,
                    ]);
                }
            }
            DB::commit();
            return $this->sendSuccess(new MonitoringResource($monitoring), 'Monitoring created');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function getChecklist(): JsonResponse
    {
        try {
            $blockId = request('block_id');
            return $this->sendSuccess($this->questionService->getQuestionList(blockId: $blockId, type: null, block_type: request('block_type')), 'Checklist');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function getChecklistRegular(): JsonResponse
    {
        try {
            return $this->sendSuccess($this->questionService->getQuestionList(null, QuestionTypeEnum::MULTIPLY), 'Checklist');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getLine());
        }
    }

    public function sendCheckListFile(): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = request()->all();
            $object = Article::query()->findOrFail($data['object_id']);
            foreach ($data['regular_checklist'] as $item) {
                if (isset($item['checklist_id'])) {
                    $answer = CheckListAnswer::query()->findOrFail($item['checklist_id']);
                    $answer->update([
                        'status' => CheckListStatusEnum::FIRST,
                        'inspector_answered' => null,
                        'technic_answered' => null,
                        'author_answered' => null,
                    ]);
                    $answer->images()->delete();
                    $answer->documents()->delete();
                } else {
                    $answer = new CheckListAnswer();
                    $answer->question_id = $item['question_id'];
                    $answer->comment = $item['comment'];
                    $answer->block_id = $data['block_id'] ?? null;
                    $answer->work_type_id = $item['work_type_id'];
                    $answer->object_id = $data['object_id'];
                    $answer->object_type_id = $object->object_type_id;
                    $answer->floor = $item['floor'] ?? null;
                    $answer->status = CheckListStatusEnum::FIRST;
                    $answer->type = isset($data['type']) ? 2 : 1;
                    $answer->monitoring_id = isset($data['type']) ? $data['claim_id'] : null;
                    $answer->save();
                }

                $author = $object->users()->where('role_id', UserRoleEnum::MUALLIF->value)->get();
                $this->sendSms($object, $author);
                $technic = $object->users()->where('role_id', UserRoleEnum::MUALLIF->value)->get();
                $this->sendSms($object, $technic);

                $tableId = $this->historyService->createHistory(
                    guId: $answer->id,
                    status: $answer->status->value,
                    type: isset($data['type']) ? LogType::CLAIM_HISTORY : LogType::TASK_HISTORY,
                    date: null,
                    comment: $item['comment'] ?? ""
                );

                $history = CheckListHistory::query()->findOrFail($tableId);

                if (!empty($item['files'])) {
                    foreach ($item['files'] as $document) {
                        $path = $document->store('documents/checklist', 'public');
                        $answer->documents()->create(['url' => $path]);
                        $history->documents()->create(['url' => $path]);
                    }
                }
                if (!empty($item['images'])) {
                    foreach ($item['images'] as $image) {
                        $path = $image->store('images/checklist', 'public');
                        $answer->images()->create(['url' => $path]);
                        $history->images()->create(['url' => $path]);
                    }
                }
            }
            DB::commit();
            return $this->sendSuccess([], 'Check list files sent');

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendError($exception->getMessage(), $exception->getLine());
        }
    }

    public function getChecklistAnswer(): JsonResponse
    {
        try {
            $data = CheckListAnswer::query()->findOrFail(request('id'));
            return $this->sendSuccess(CheckListAnswerFilesResource::make($data), 'Checklist Answer');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    private function sendSms($object, $user)
    {
        try {
            $message = MessageTemplate::checklistCreated($object->task_id);
            (new SmsService($user->phone, $message))->sendSms();
        }catch (\Exception $exception) {}

    }


}
