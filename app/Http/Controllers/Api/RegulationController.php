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
use App\Services\RegulationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Queue\Jobs\SqsJob;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
            $user = Auth::user();
            $roleId = $user->getRoleFromToken();

            $query = Regulation::query();
            switch ($roleId) {
                case 26:
                    $query->whereHas('monitoring', function ($q) use ($user) {
                        $q->whereHas('article', function ($articleQuery) use ($user) {
                            $articleQuery->where('region_id', $user->region_id);
                        });
                    });
                    break;
                case 27:
//                    case 28:
                    $query->where('regulation_status_id', request('status'));
                    break;
                default:
                    $query->where(function ($q) use ($user) {
                        $q->where('regulations.user_id', $user->id)
                            ->orWhere('regulations.created_by_user_id', $user->id)
                            ->orWhere('regulations.role_id', $user->getRoleFromToken())
                            ->orWhere('regulations.created_by_role_id', $user->getRoleFromToken());
                    });
                    break;
            }

            $query->when(request('object_name'), function ($q) {
                $q->whereHas('monitoring.article', function ($query) {
                    $query->where('name', 'like', '%' . request('object_name') . '%');
                });
            })
                ->when(request('region_id'), function ($q) {
                    $q->whereHas('monitoring.article', function ($query) {
                        $query->where('region_id', request('region_id'));
                    });
                })
                ->when(request('district_id'), function ($q) {
                    $q->whereHas('monitoring.article', function ($query) {
                        $query->where('district_id', request('district_id'));
                    });
                })
                ->when(request('organization_name'), function ($q) {
                    $q->whereHas('monitoring.article', function ($query) {
                        $query->where('organization_name', 'like', '%' . request('organization_name') . '%');
                    });
                })
                ->when(request('funding_source'), function ($q) {
                    $q->whereHas('monitoring.article', function ($query) {
                        $query->where('funding_source_id', request('funding_source'));
                    });
                })
                ->when(request('category'), function ($q) {
                    $q->whereHas('monitoring.article', function ($query) {
                        $query->where('difficulty_category_id', request('category'));
                    });
                })
                ->when(request('status'), function ($query) {
                    $query->where('regulation_status_id', request('status'));
                });

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

    public function getAuthorRegulations(): JsonResponse
    {
        try {
            $authorRegulations = AuthorRegulation::query()->paginate(request('per_page', 10));
            return $this->sendSuccess(AuthorRegulationResource::collection($authorRegulations), 'Regulations', pagination($authorRegulations));
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function getRegulation($id): JsonResponse
    {
        try {
            $regulation = Regulation::query()->findOrFail($id);
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
        try {
            $files = [];
            $regulation = AuthorRegulation::query()->findOrFaiL(request('regulation_id'));
            if (request()->hasFile('files')) {
                foreach (request()->file('files') as $file) {
                    $path = $file->store('images/author-regulation', 'public');
                    $files[] = $path;
                }
            }
            $regulation->update([
                'comment' => request('comment'),
                'images' => json_encode($files),
            ]);

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
        $data = getData(config('app.gasn.rating'), \request('inn'));
        return $data['data']['data'];
    }
}
