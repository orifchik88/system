<?php

namespace App\Http\Controllers\Api;

use App\Enums\QuestionTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\MonitoringRequest;
use App\Http\Resources\LevelResource;
use App\Http\Resources\MonitoringResource;
use App\Models\Article;
use App\Models\CheckList;
use App\Models\CheckListAnswer;
use App\Models\Level;
use App\Models\Monitoring;
use App\Services\QuestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MonitoringController extends BaseController
{
    private QuestionService $questionService;

    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }

    public function monitoring(): JsonResponse
    {
        $monitorings = Monitoring::query()->where('object_id', \request('object_id'))->paginate(request('per_page', 10));
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


            if ($request->hasFile('images'))
            {
                foreach ($request->file('images') as $image){
                    $imagePath = $image->store('monitoring', 'public');
                    $monitoring->images()->create([
                        'url' => $imagePath,
                    ]);
                }
            }
            DB::commit();
            return $this->sendSuccess(new MonitoringResource($monitoring), 'Monitoring created');
        }catch (\Exception $exception){
            DB::rollBack();
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function  getChecklist(): JsonResponse
    {
        try {
            $blockId = request('block_id');
            return $this->sendSuccess($this->questionService->getQuestionList($blockId), 'Checklist');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }


    }

    public function getChecklistForTechnic(): JsonResponse
    {
        $blockId = request('block_id');
        $levels = Level::with(['checklists', 'levelStatus'])->where('block_id', $blockId)
            ->whereHas('checklists.question', function ($query) {
                $query->where('type', QuestionTypeEnum::COMMON);
            })->get();
        return $this->sendSuccess(LevelResource::collection($levels),'All Data');
    }

    public function getChecklistRegular(): JsonResponse
    {
        $blockId = request('block_id');
        $levels = Level::with(['checklists', 'levelStatus'])->where('block_id', $blockId)
            ->whereHas('checklists.question', function ($query) {
                $query->where('type', QuestionTypeEnum::MULTIPLY);
            })->get();
        return $this->sendSuccess(LevelResource::collection($levels),'All Data');
    }

    public function sendCheckListFile(): JsonResponse
    {
        try {

            $data = request()->all();
            $answer = new CheckListAnswer();
            $answer->question_id = $data['question_id'];
            $answer->block_id = $data['block_id'];
            $answer->work_type_id = $data['work_type_id'];
            $answer->object_id = $data['object_id'];
            $answer->object_type_id = $data['object_type_id'];
            $answer->floor = $data['floor'];
            $answer->save();

            foreach ($data['regular_checklist'] as $item) {

               if (!empty($item['files'])){
                   foreach ($item['files'] as $document) {
                       $path = $document->store('documents/checklist', 'public');
                       $answer->documents()->create(['url' => $path]);
                   }
               }
                if (!empty($item['images'])){
                    foreach ($item['images'] as $image) {
                        $path = $image->store('images/checklist', 'public');
                        $answer->images()->create(['url' => $path]);
                    }
                }
            }

            return $this->sendSuccess([], 'Check list files sent');

        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }


}
