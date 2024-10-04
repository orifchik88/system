<?php

namespace App\Http\Controllers\Api;

use App\DTO\QuestionDto;
use App\DTO\RegulationDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegulationAcceptRequest;
use App\Http\Requests\RegulationDemandRequest;
use App\Http\Resources\MonitoringResource;
use App\Http\Resources\RegulationResource;
use App\Http\Resources\ViolationResource;
use App\Models\ActViolation;
use App\Models\Article;
use App\Models\Monitoring;
use App\Models\Regulation;
use App\Models\RegulationDemand;
use App\Models\Violation;
use App\Services\RegulationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Queue\Jobs\SqsJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Js;
use Spatie\Permission\Models\Role;

class RegulationController extends BaseController
{

    public function __construct(protected RegulationService $regulationService){}



    public function regulations(): JsonResponse
    {
        try {
            $user = Auth::user();

            $roleId = $user->getRoleFromToken();

            if ($roleId == 26)
            {
                $regulations = Regulation::query()
                    ->whereHas('monitoring', function ($query) use($user){
                        $query->whereHas('article', function ($articleQuery) use($user){
                            $articleQuery->where('region_id', $user->region_id);
                        });
                    })
                    ->paginate(request('per_page', 10));

                return $this->sendSuccess(
                    RegulationResource::collection($regulations),
                    'Regulations',
                    pagination($regulations)
                );
            }

            $query = $user->regulations();

            $regulations = $query->paginate(request('per_page', 10));

            return $this->sendSuccess(
                RegulationResource::collection($regulations),
                'Regulations',
                pagination($regulations)
            );

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function getRegulation($id): JsonResponse
    {
        try {
            $regulation = Regulation::query()->findOrFail($id);
            return $this->sendSuccess(new RegulationResource($regulation), 'Regulation found');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }


    public function askDate(RegulationDemandRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $regulation = Regulation::query()->findOrFaiL($request->post('regulation_id'));

            if ($regulation->deadline_asked) return $this->sendError('Muddat oldin  soralgan');

            RegulationDemand::query()->create([
                'user_id' => Auth::id(),
                'regulation_id' => $regulation->id,
                'act_status_id' => 10,
                'act_violation_type_id' => 3,
                'status' => ActViolation::PROGRESS,
                'comment' => $request->comment
            ]);

            $regulation->update([
                'deadline_asked' => true,
                'act_status_id' => 10
            ]);

            DB::commit();
            return $this->sendSuccess([], 'Data saved successfully');

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendError($exception->getMessage(), $exception->getCode());

        }
    }

    public function acceptDate(RegulationAcceptRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $regulation = Regulation::query()->findOrFaiL($request->post('regulation_id'));

            RegulationDemand::query()->create([
                'user_id' => Auth::id(),
                'regulation_id' => $regulation->id,
                'act_violation_type_id' => 3,
                'act_status_id' => 8,
                'status' => ActViolation::ACCEPTED,
                'deadline' => $request->post('deadline'),
                'comment' => $request->post('comment')
            ]);

            $regulation->update([
                'act_status_id' => 8,
                'deadline' => $request->post('deadline')
            ]);

            DB::commit();
            return $this->sendSuccess([], 'Data saved successfully');

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function rejectDate(RegulationDemandRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $regulation = Regulation::query()->findOrFaiL($request->post('regulation_id'));

            RegulationDemand::query()->create([
                'user_id' => Auth::id(),
                'regulation_id' => $regulation->id,
                'act_violation_type_id' => 3,
                'act_status_id' => 9,
                'status' => ActViolation::REJECTED,
                'comment' => $request->post('comment')
            ]);

            $regulation->update([
                'act_status_id' => 9,
                'deadline' => $request->post('deadline')
            ]);

            DB::commit();
            return $this->sendSuccess([], 'Data saved successfully');

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function acceptAnswer(): JsonResponse
    {
        try {
            $dto = new RegulationDto();

            $dto->setRegulationId(request('regulation_id'))
                ->setComment(request('comment'));

            $this->regulationService->acceptAnswer($dto);

            return $this->sendSuccess([], "Data saved successfully");
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function acceptDeed(): JsonResponse
    {
        try {
            $dto = new RegulationDto();

            $dto->setRegulationId(request('regulation_id'))
                ->setComment(request('comment'));

            $this->regulationService->acceptDeed($dto);

            return $this->sendSuccess([], 'Data saved successfully');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }



    public function rejectAnswer(): JsonResponse
    {
        try {
            $dto = new RegulationDto();
            $dto->setRegulationId(request('regulation_id'))
                ->setComment(request('comment'));


            $this->regulationService->rejectToAnswer($dto);

            return $this->sendSuccess([], 'Data saved successfully');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }

    }

    public function sendDeed(): JsonResponse
    {
        try {
            $dto = new RegulationDto();
            $dto->setRegulationId(request('regulation_id'))
                ->setMeta(request('violations'));


            $this->regulationService->sendToDeed($dto);

            return $this->sendSuccess([], 'Data saved successfully');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function rejectDeed(): JsonResponse
    {
        try {
            $dto = new RegulationDto();
            $dto->setRegulationId(request('regulation_id'))
                ->setComment(request('comment'));

            $this->regulationService->rejectDeed($dto);

            return $this->sendSuccess([], 'Data saved successfully');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function regulationOwner(): JsonResponse
    {
        try {
            if (request('id')) {
                $regulation = Regulation::query()->findOrFaiL(request('id'));
                return $this->sendSuccess(RegulationResource::make($regulation), 'Get data successfully');
            }

            if (request('object_id')){
                $object = Article::query()->findOrFaiL(request('object_id'));
                $data = $object->regulations()->where('created_by_user_id', Auth::id())->paginate(request('per_page', 10));
                return $this->sendSuccess(RegulationResource::collection($data), 'Get data successfully', pagination($data));
            }

            $regulations = Regulation::query()
                ->where('created_by_user_id', Auth::id())
                ->whereIn('act_status_id', [1,4,7])
                ->paginate(request('per_page', 10));
            return $this->sendSuccess(
                RegulationResource::collection($regulations),
                'Regulations',
                pagination($regulations)
            );
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }


    public function test()
    {
       $user = Auth::user();
       $role = $user->getRoleFromToken();
       dd($role);
    }
}
