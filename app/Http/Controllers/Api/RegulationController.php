<?php

namespace App\Http\Controllers\Api;

use App\DTO\QuestionDto;
use App\DTO\RegulationDto;
use App\Enums\LawyerStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegulationAcceptRequest;
use App\Http\Requests\RegulationDemandRequest;
use App\Http\Requests\RegulationFineRequest;
use App\Http\Resources\AuthorRegulationResource;
use App\Http\Resources\CheckListAnswerResource;
use App\Http\Resources\ChecklistResource;
use App\Http\Resources\MonitoringResource;
use App\Http\Resources\RegulationResource;
use App\Http\Resources\ViolationResource;
use App\Models\ActViolation;
use App\Models\Article;
use App\Models\AuthorRegulation;
use App\Models\CheckListAnswer;
use App\Models\Monitoring;
use App\Models\Regulation;
use App\Models\RegulationDemand;
use App\Models\RegulationFine;
use App\Models\Violation;
use App\Notifications\InspectorNotification;
use App\Services\RegulationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Queue\Jobs\SqsJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Js;
use Spatie\Permission\Models\Role;

class RegulationController extends BaseController
{

    public function __construct(protected RegulationService $regulationService)
    {
        $this->middleware('auth');
        parent::__construct();
    }


    public function regulations(): JsonResponse
    {
        try {
            $query = $this->regulationService->getRegulations($this->user, $this->roleId);
            $filters = request()->only(['object_name', 'region_id', 'district_id', 'organization_name', 'funding_source', 'category', 'status', 'lawyer_status']);

            $regulations = $this->regulationService->searchRegulations($query, $filters)
                ->orderBy('created_at', request('sort_by_date', 'DESC'))
                ->paginate(request('per_page', 10));

            return $this->sendSuccess(
                RegulationResource::collection($regulations),
                'Regulations',
                pagination($regulations)
            );

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function lawyerCount(): JsonResponse
    {
        try {
            $query =  $this->regulationService->getRegulations($this->user, $this->roleId);
            $data = [
                'new' => $query->clone()->where('lawyer_status_id', LawyerStatusEnum::NEW)->count(),
                'process' => $query->clone()->where('lawyer_status_id', LawyerStatusEnum::PROCESS)->count(),
                'administrative' => $query->clone()->where('lawyer_status_id', LawyerStatusEnum::ADMINISTRATIVE)->count(),
                'disassembly' => $query->clone()->where('lawyer_status_id', LawyerStatusEnum::DISASSEMBLY)->count(),
            ];
            return $this->sendSuccess($data, 'Lawyer Count');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function regulationCount(): JsonResponse
    {
        try {
            return $this->sendSuccess($this->regulationService->regulationCountByStatus($this->user, $this->roleId), 'Regulation Count');
        } catch (\Exception $exception){
            return $this->sendError($exception->getMessage());
        }
    }

    public function getAuthorRegulations(): JsonResponse
    {
        try {
            $authorRegulations = AuthorRegulation::query()->where('object_id', request('object_id'))->orderBy('id', 'DESC')->paginate(request('per_page', 10));
            return $this->sendSuccess(AuthorRegulationResource::collection($authorRegulations), 'Regulations', pagination($authorRegulations));
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function getRegulation($id): JsonResponse
    {
        try {
            $regulation = $this->regulationService->getRegulationById($this->user, $this->roleId, $id);
            return $this->sendSuccess(new RegulationResource($regulation), 'Regulation found');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }


    public function askDate(RegulationDemandRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();
            $roleId = $user->getRoleFromToken();

            $regulation = Regulation::query()->findOrFaiL($request->post('regulation_id'));

            if ($regulation->deadline_asked) return $this->sendError('Muddat oldin  soralgan');

            $act = ActViolation::create([
                'regulation_id' => $regulation->id,
                'user_id' => Auth::id(),
                'act_status_id' => 10,
                'comment' => $request->comment,
                'role_id' => $roleId,
                'act_violation_type_id' => 3,
                'status' => ActViolation::PROGRESS,
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
            $user = Auth::user();
            $roleId = $user->getRoleFromToken();

            $regulation = Regulation::query()->findOrFaiL($request->post('regulation_id'));
            $act = ActViolation::create([
                'regulation_id' => $regulation->id,
                'user_id' => Auth::id(),
                'act_status_id' => 11,
                'comment' => $request->comment,
                'role_id' => $roleId,
                'act_violation_type_id' => 3,
                'status' => ActViolation::ACCEPTED,
            ]);

            $regulation->update([
                'act_status_id' => 11,
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
            $user = Auth::user();
            $roleId = $user->getRoleFromToken();


            $act = ActViolation::create([
                'regulation_id' => $regulation->id,
                'user_id' => Auth::id(),
                'act_status_id' => 12,
                'comment' => $request->comment,
                'role_id' => $roleId,
                'act_violation_type_id' => 3,
                'status' => ActViolation::REJECTED,
            ]);


            $regulation->update([
                'act_status_id' => 12,
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
        } catch (\Exception $exception) {
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
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function acceptDeedCmr(): JsonResponse
    {
        try {
            $dto = new RegulationDto();

            $dto->setRegulationId(request('regulation_id'))
                ->setComment(request('comment'));

            $this->regulationService->acceptDeedCmr($dto);

            return $this->sendSuccess([], 'Data saved successfully');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function sendAnswerAuthorRegulation(): JsonResponse
    {
        $data = request()->all();
        try {
            foreach ($data['journal'] as $item) {
                $files = [];
                $regulation = AuthorRegulation::query()->findOrFaiL($item['regulation_id']);
                if (isset($item['files'])) {
                    foreach ($item['files'] as $file) {
                        $path = $file->store('images/author-regulation', 'public');
                        $files[] = $path;
                    }
                }
                $regulation->update([
                    'comment' => $item['comment'],
                    'images' => json_encode($files),
                ]);
            }


            return $this->sendSuccess([], 'Data saved successfully');

        } catch (\Exception $exception) {
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
        } catch (\Exception $exception) {
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
        } catch (\Exception $exception) {
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
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function rejectDeedCmr(): JsonResponse
    {
        try {
            $dto = new RegulationDto();
            $dto->setRegulationId(request('regulation_id'))
                ->setComment(request('comment'));

            $this->regulationService->rejectDeedCmr($dto);

            return $this->sendSuccess([], 'Data saved successfully');
        } catch (\Exception $exception) {
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

            if (request('object_id')) {
                $object = Article::query()->findOrFaiL(request('object_id'));
                $data = $object->regulations()->where('created_by_user_id', Auth::id())->paginate(request('per_page', 10));
                return $this->sendSuccess(RegulationResource::collection($data), 'Get data successfully', pagination($data));
            }

            $regulations = Regulation::query()
                ->where('created_by_user_id', Auth::id())
                ->whereIn('act_status_id', [1, 4, 7])
                ->paginate(request('per_page', 10));
            return $this->sendSuccess(
                RegulationResource::collection($regulations),
                'Regulations',
                pagination($regulations)
            );
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function fine(RegulationFineRequest $request): JsonResponse
    {
        try {
            $regulation = Regulation::query()->findOrFaiL($request->regulation_id);

            $fine = new RegulationFine();
            $fine->regulation_id = $regulation->id;
            $fine->organization_name = $request->organization_name;
            $fine->user_type = $request->user_type;
            $fine->inn = $request->inn;
            $fine->full_name = $request->full_name;
            $fine->pinfl = $request->pinfl;
            $fine->position = $request->position;
            $fine->decision_series = $request->decision_series;
            $fine->decision_number = $request->decision_number;
            $fine->substance = $request->substance;
            $fine->substance_item = $request->substance_item;
            $fine->amount = $request->amount;
            $fine->date = $request->date;
            $fine->save();

            if ($request->hasFile('images')) {
                foreach ($request->images as $image) {
                    $path = $image->store('images/fines', 'public');
                    $fine->images()->create(['url' => $path]);
                }
            }

            if ($request->hasFile('files')) {
                foreach ($request->files as $document) {
                    $path = $document->store('document/fines', 'public');
                    $fine->documents()->create(['url' => $path]);
                }
            }

            $demand = RegulationDemand::query()->where('regulation_id', $regulation->id)->latest()->first();

            if (!$demand) {
                $status = 1;
            } elseif ($demand->act_violation_type_id = 1) {
                $status = 1;
            } elseif ($demand->act_violation_type_id = 1) {
                $status = 3;

            } else {
                $status = 1;
            }

            $regulation->update([
                'deadline' => null,
                'lawyer_status_id' => LawyerStatusEnum::ADMINISTRATIVE,
                'regulation_status_id' => $status,
            ]);


            return $this->sendSuccess([], 'Data saved successfully');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function sendCourt(): JsonResponse
    {
        try {
            $regulation = Regulation::query()->findOrFaiL(request('regulation_id'));

            $regulation->update([
                'lawyer_status_type' => request('type'),
                'lawyer_status_id' => LawyerStatusEnum::PROCESS
            ]);

            return $this->sendSuccess([], 'Data saved successfully');

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }


    public function test()
    {
        try {
            $user = Auth::user();
            $user->notify(new InspectorNotification(title: 'Nazorat', message: 'Test', url: null, additionalInfo: null));

//            $response = Http::withHeaders([
//                'Content-Type' => 'application/json; charset=utf-8',
//                'Authorization' => 'ODFjNmNkOTgtMzI4OS00ZjAxLWI3YmQtNmI2Nzc0M2VlMDVi', // OneSignal REST API key
//            ])->post('https://onesignal.com/api/v1/notifications', [
//                'app_id' => 'a09289fb-95f4-4e89-a860-b66bcd773242',
//                'include_external_user_ids' => ['951663ea-6822-4c42-8b8d-904645187f8a'],
//                'data' => [
//                      'screen' => '/tutorial'
//                ],
//
////                'included_segments' => ['All'], // Bu barcha subscribed foydalanuvchilarga yuboradi
//                'contents' => [
//                    'en' => "Ajara guju", // Xabar matni
//                ],
//                'big_picture' => 'https://pmtips.net/Portals/0/EasyDNNNews/2137/700600p546EDNmainimg-3-types-of-tools-for-project-task-management1.jpg',
//                'headings' => [
//                    'en' => "Nazorat",
//                ]
//            ]);
//
//            if ($response->successful()) {
//                return $response->json();
//            } else {
//                return $response->body();  // Xatolik yuz bersa, ma'lumotni ko'rsatadi.
//            }
        } catch (\Exception $exception) {
            Log::info($exception->getMessage());
        }
    }
}
