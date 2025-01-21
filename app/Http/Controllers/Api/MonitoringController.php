<?php

namespace App\Http\Controllers\Api;

use App\Enums\CheckListStatusEnum;
use App\Enums\LogType;
use App\Enums\QuestionTypeEnum;
use App\Enums\UserRoleEnum;
use App\Enums\WorkTypeStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\MonitoringRequest;
use App\Http\Resources\CheckListAnswerFilesResource;
use App\Http\Resources\CheckListAnswerResource;
use App\Http\Resources\CheckListHistoryFileResource;
use App\Http\Resources\CheckListHistoryResource;
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
use App\Services\MonitoringService;
use App\Services\QuestionService;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\isFalse;

class MonitoringController extends BaseController
{
    private HistoryService $historyService;

    public function __construct(
        protected QuestionService   $questionService,
        protected MonitoringService $monitoringService)
    {
        $this->middleware('auth');
        parent::__construct();
        $this->historyService = new HistoryService('check_list_histories');
    }

    public function monitoringList()
    {
        try {
            $filters = request()->only(['funding_source_id', 'difficulty_category_id', 'inspector_id', 'year', 'month', 'own']);
            $monitorings = $this->monitoringService->getMonitoringList(filters: $filters);

            return $this->sendSuccess($monitorings, 'Monitorings');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage());
        }
    }

    public function monitoring(): JsonResponse
    {
        try {
            $query = $this->monitoringService->getMonitorings($this->user, $this->roleId);
            $filters = request()->only(['object_name', 'region_id', 'district_id', 'funding_source', 'category', 'task_id']);

            $monitorings = $this->monitoringService->searchMonitoring($query, $filters)
                ->orderBy('created_at', request('sort_by_date', 'DESC'))
                ->paginate(request('per_page', 10));

            return $this->sendSuccess(MonitoringResource::collection($monitorings), 'Monitorings', pagination($monitorings));
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage());
        }

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

    public function getChecklistLog(): JsonResponse
    {
        try {
            $checklist = CheckListAnswer::query()->findOrFail(request('checklist_id'));
            $logs = CheckListHistoryResource::collection($checklist->logs()->orderBy('created_at')->get());

            return $this->sendSuccess($logs, 'Checklist logs');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function getChecklistLogFile(): JsonResponse
    {
        try {
            $history = CheckListHistory::query()->findOrFail(request('history_id'));

            return $this->sendSuccess(CheckListHistoryFileResource::make($history), 'Files');

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function getChecklistRegular(): JsonResponse
    {
        try {
            return $this->sendSuccess($this->questionService->getQuestionList(null, QuestionTypeEnum::MULTIPLY, 3), 'Checklist');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getLine());
        }
    }

    public function sendCheckListFile(): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = request()->all();
            $inspectorAnswered = null;
            $technicAnswered = null;

            $object = Article::query()->findOrFail($data['object_id']);
            foreach ($data['regular_checklist'] as $item) {
                if (isset($item['status'])) {
                    $status = CheckListStatusEnum::SECOND;
                    $inspectorAnswered = now()->addDays(3)->setTime(23, 59, 59);
                } else {
                    $status = CheckListStatusEnum::FIRST;
                    $technicAnswered = now()->addDays(3)->setTime(23, 59, 59);
                }
                if (isset($item['checklist_id'])) {
                    $answer = CheckListAnswer::query()->findOrFail($item['checklist_id']);

                    $answer->update([
                        'status' => $status,
                        'inspector_answered' => null,
                        'technic_answered' => null,
                        'author_answered' => null,
                        'inspector_answered_at' => $inspectorAnswered,
                        'technic_author_answered_at' => $technicAnswered,
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
                    $answer->status = $status;
                    $answer->type = isset($data['type']) ? 2 : 1;
                    $answer->monitoring_id = isset($data['type']) ? $data['claim_id'] : null;
                    $answer->inspector_answered_at = $inspectorAnswered;
                    $answer->technic_author_answered_at = $technicAnswered;
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

    public function acceptWorkType()
    {
        DB::beginTransaction();
        try {
            $data = request()->all();
            $object = Article::query()->findOrFail($data['object_id']);
            $block = Block::query()->findOrFail($data['block_id']);

            $block->update([
                'selected_work_type' => true
            ]);
            foreach ($data['regular_checklist'] as $item) {

                $answer = CheckListAnswer::query()
                    ->where('work_type_id', $item['work_type_id'])
                    ->where('question_id', $item['question_id'])
                    ->where('floor', $item['floor'])
                    ->where('block_id', $data['block_id'])
                    ->first();

                if ($answer) {
                    $answer->update([
                        'comment' => $item['comment'] ?? null,
                        'status' => CheckListStatusEnum::CONFIRMED,
                        'type' => isset($data['type']) ? 2 : 1,
                        'inspector_answered' => 1
                    ]);
                } else {
                    $answer = new CheckListAnswer();
                    $answer->question_id = $item['question_id'];
                    $answer->comment = $item['comment'] ?? null;
                    $answer->block_id = $data['block_id'] ?? null;
                    $answer->work_type_id = $item['work_type_id'];
                    $answer->object_id = $data['object_id'];
                    $answer->object_type_id = $object->object_type_id;
                    $answer->floor = $item['floor'] ?? null;
                    $answer->status = CheckListStatusEnum::CONFIRMED;
                    $answer->type = isset($data['type']) ? 2 : 1;
                    $answer->inspector_answered = 1;
                    $answer->save();
                }


                $this->historyService->createHistory(guId: $answer->id,
                    status: $answer->status->value,
                    type: isset($data['type']) ? LogType::CLAIM_HISTORY : LogType::TASK_HISTORY,
                    date: null,
                    comment: $item['comment'] ?? ""
                );
            }

            $workTypes = $this->questionService->getQuestionList($data['block_id']);
            $block = Block::query()->find($data['block_id']);
            $count = 0;
            foreach ($workTypes as $workType) {
                if ($workType['questions'][0]['work_type_status'] == WorkTypeStatusEnum::CONFIRMED) {
                    $count += 1;
                }
            }
            if ($count >= count($workTypes)) {
                $block->update([
                    'status' => false
                ]);
            }
            DB::commit();
            return $this->sendSuccess([], 'Check accepted');

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendError($exception->getMessage(), $exception->getLine());
        }
    }

    public function acceptBlocks(): JsonResponse
    {
        $blocks = Block::whereIn('id', function ($query) {
            $query->select('block_id')
                ->from('check_list_answers');
        })
        ->where('status', true)
        ->where('selected_work_type', true)
        ->chunk(100, function ($blocks) {
            foreach ($blocks as $block) {
                $workTypes = $this->questionService->getQuestionList($block);
                $block = Block::query()->find($data['block_id']);
                $count = 0;
                foreach ($workTypes as $workType) {
                    if ($workType['questions'][0]['work_type_status'] == WorkTypeStatusEnum::CONFIRMED) {
                        $count += 1;
                    }
                }
                if ($count >= count($workTypes)) {
                    $block->update([
                        'status' => false
                    ]);
                }
            }
        });

        return $this->sendSuccess([], 'adsf');
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
        } catch (\Exception $exception) {
        }

    }


}
