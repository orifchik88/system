<?php

namespace App\Http\Controllers\Api;

use App\Enums\QuestionTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\MonitoringRequest;
use App\Http\Resources\LevelResource;
use App\Http\Resources\MonitoringResource;
use App\Models\Article;
use App\Models\CheckList;
use App\Models\Level;
use App\Models\Monitoring;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MonitoringController extends BaseController
{
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
        $blockId = request('block_id');
        $levels = Level::with(['checklists', 'levelStatus'])->where('block_id', $blockId)
            ->whereHas('checklists.question', function ($query) {
                $query->whereIn('type', [QuestionTypeEnum::BLOCK, QuestionTypeEnum::LINEAR]);
            })->get();
        return $this->sendSuccess(LevelResource::collection($levels),'All Data');
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
            $data = request('regular_checklist');
            foreach ($data as $item) {
               $checklist =  CheckList::query()->find($item['checklist_id']);
               if (!empty($item['files'])){
                   foreach ($item['files'] as $document) {
                       $path = $document->store('documents/checklist', 'public');
                       $checklist->documents()->create(['url' => $path]);
                   }
               }
                if (!empty($item['images'])){
                    foreach ($item['images'] as $image) {
                        $path = $image->store('images/checklist', 'public');
                        $checklist->images()->create(['url' => $path]);
                    }
                }
            }

            return $this->sendSuccess([], 'Check list files sent');

        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

}
