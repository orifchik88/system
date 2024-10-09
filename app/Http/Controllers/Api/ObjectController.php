<?php

namespace App\Http\Controllers\Api;

use App\DTO\ObjectDto;
use App\Enums\LogType;
use App\Enums\ObjectCheckEnum;
use App\Enums\ObjectStatusEnum;
use App\Enums\UserRoleEnum;
use App\Http\Requests\ObjectRequest;
use App\Http\Resources\ArticleResource;
use App\Http\Resources\FundingSourceResource;
use App\Http\Resources\ObjectSectorResource;
use App\Http\Resources\ObjectStatusResource;
use App\Http\Resources\ObjectTypeResource;
use App\Models\Article;
use App\Models\ArticlePaymentLog;
use App\Models\ObjectStatus;
use App\Models\UserRole;
use App\Services\ArticleService;
use App\Services\HistoryService;
use Illuminate\Http\FileHelpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ObjectController extends BaseController
{

    private HistoryService $historyService;

    public function __construct(
        protected ArticleService $service,
    )
    {
        $this->historyService = new HistoryService('article_payment_logs');
    }

    public function index(): JsonResponse
    {
        $user = Auth::user();
        $roleId = $user->getRoleFromToken();

        if (UserRoleEnum::from($roleId) == UserRoleEnum::REGISTRATOR) {
            $query = Article::query()->where('region_id', $user->region_id);
        } else {
            $query = $user->objects()
                ->wherePivot('role_id', $roleId);
        }

        $objects = $query->when(request('status'), function ($query) {
            $query->where('articles.object_status_id', request('status'));
        })
            ->when(request('name'), function ($query) {
                $query->searchByName(request('name'));
            })
            ->when(request('task_id'), function ($query) {
                $query->searchByTaskId(request('task_id'));
            })
            ->when(request('region_id'), function ($query) {
                $query->where('articles.region_id', request('region_id'));
            })
            ->when(request('district_id'), function ($query) {
                $query->where('articles.district_id', request('district_id'));
            })
            ->when(request('user_search'), function ($query) {
                $query->whereHas('users', function ($query) {
                    $query->searchByFullName(request('user_search'));
                });
            })
            ->paginate(\request('perPage', 10));
        return $this->sendSuccess(ArticleResource::collection($objects), 'Objects retrieved successfully.', pagination($objects));
    }

    public function accountObjects(): JsonResponse
    {
        $user = Auth::user();
        $query = Article::query()->where('region_id', $user->region_id);

            $objects = $query
                ->when(request('name'), function ($query) {
                $query->searchByName(request('name'));
            })
            ->when(request('task_id'), function ($query) {
                $query->searchByTaskId(request('task_id'));
            })
            ->when(request('region_id'), function ($query) {
                $query->where('articles.region_id', request('region_id'));
            })
            ->when(request('district_id'), function ($query) {
                $query->where('articles.district_id', request('district_id'));
            })
            ->when(request('user_search'), function ($query) {
                $query->whereHas('users', function ($query) {
                    $query->searchByFullName(request('user_search'));
                });
            })
                ->when(request('status'), function ($query) {
                    $status = request('status');
                    if ($status == 1) {
                        $query->whereDoesntHave('paymentLogs');
                    } elseif ($status == 2) {
                        $query->whereHas('paymentLogs', function ($query) {
                            $query->select(DB::raw('SUM(CAST(content->additionalInfo->amount AS DECIMAL)) as total_paid'))
                                ->having('total_paid', '<', DB::raw('price_supervision_service'));
                        });
                    } elseif ($status == 3) {
                        $query->whereHas('paymentLogs', function ($query) {
                            $query->select(DB::raw('SUM(CAST(content->additionalInfo->amount AS DECIMAL)) as total_paid'))
                                ->having('total_paid', '>=', DB::raw('price_supervision_service'));
                        });
                    }
                })
            ->paginate(\request('perPage', 10));
        return $this->sendSuccess(ArticleResource::collection($objects), 'Objects retrieved successfully.', pagination($objects));
    }

    public function totalPayment(): JsonResponse
    {
        try {
            $totalPaid = Article::with('paymentLogs')
                ->where('region_id', request('region_id'))
                ->get()
                ->reduce(function ($carry, $article) {
                    return $carry + $article->paymentLogs->sum(function ($log) {
                            return isset($log->content->additionalInfo->amount)
                                ? (float) $log->content->additionalInfo->amount
                                : 0;
                        });
                });

            $totalAmount = Article::where('region_id', request('region_id'))->get()->sum(function ($article) {
                return (float)$article->price_supervision_service;
            });

            return $this->sendSuccess([
                'totalAmount' => $totalAmount,
                'totalPaid' => $totalPaid,
                'notPaid' => $totalAmount - $totalPaid,
            ],
                'All Payments'
            );
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function paymentStatistics(): JsonResponse
    {
        try {
            $regionId = request('region_id');

            $articles = Article::with('paymentLogs')
                ->where('region_id', $regionId)
                ->get();

            $statistics = [
                'all' => $articles->count(),
                'paid' => 0,
                'partiallyPaid' => 0,
                'notPaid' => 0,
            ];

            foreach ($articles as $article) {
                $totalPaid = $article->paymentLogs()
                    ->get()
                    ->sum(function ($log) {
                        return $log->content->additionalInfo->amount ?? 0;
                    });

                $priceSupervisionService = (float)$article->price_supervision_service;

                if ($totalPaid >= $priceSupervisionService) {
                    $statistics['paid']++;
                } elseif ($totalPaid < $priceSupervisionService && $totalPaid > 0) {
                    $statistics['partiallyPaid']++;
                } else {
                    $statistics['notPaid']++;
                }
            }

            return $this->sendSuccess($statistics, 'Payment Statistics');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function getObject($id): JsonResponse
    {
        try {
            $object = Article::query()->findOrFail($id);
            return $this->sendSuccess(ArticleResource::make($object), 'Object retrieved successfully.');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function objectTypes(): JsonResponse
    {
        try {
            if (request('id')) {
                return $this->sendSuccess(ObjectTypeResource::make($this->service->getType(request('id'))), 'Object type retrieved successfully.');
            }
            return $this->sendSuccess(ObjectTypeResource::collection($this->service->getAllTypes()), 'Object types');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function objectSectors($id): JsonResponse
    {
        try {
            return $this->sendSuccess(ObjectSectorResource::collection($this->service->getObjectSectors($id)), 'Object sectors');

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function fundingSource(): JsonResponse
    {
        return $this->sendSuccess(FundingSourceResource::collection($this->service->getAllFundingSources()), 'Funding sources');
    }

    public function create(ObjectRequest $request): JsonResponse
    {
        try {
            $dto = new ObjectDto();
            $dto->setResponseId($request->response_id);

            $this->service->setObjectDto($dto);


            $object = $this->service->createObject();
            return $this->sendSuccess($object, 'Object created');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function checkObject()
    {
        try {
            $object = Article::findOrFail(request()->get('id'));

            $missingRoles = $this->checkUsers($object);
            $blocks = $this->checkBlocks($object);

            if (!empty($missingRoles)) {
                return $this->sendError('Obyekt qatnashchilari yetarli emas ' . implode(', ', $missingRoles));
            }

            if (!empty($blocks)) {
                return $this->sendError('Obyekt blocklar foydalanishga topshirilgan ' . implode(', ', $blocks));
            }

            return $this->sendSuccess(ArticleResource::make($object), 'Article retrieved successfully.');

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    private function checkUsers($object): array
    {
        $users = $object->users;
        $missingRoles = [];
        foreach (ObjectCheckEnum::cases() as $role) {
            $method = $role->value;
            $hasRole = $users->contains(function ($user) use ($method) {
                return $user->{$method}();
            });
            if (!$hasRole) {
                $missingRoles[] = $role->name;
            }
        }

        return $missingRoles;
    }

    private function checkBlocks($object): array
    {
        $inactiveBlocks = [];
        foreach ($object->blocks as $block) {
            if (!$block->status) {
                $inactiveBlocks[] = $block->name;
            }
        }
        return $inactiveBlocks;
    }

    public function status(): JsonResponse
    {
        try {
            if (request('id')) {
                return $this->sendSuccess(ObjectStatusResource::make(ObjectStatus::find(request('id'))), 'Object Status');
            }
            return $this->sendSuccess(ObjectStatusResource::collection(ObjectStatus::all()), 'All Object Statuses');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function objectCount(): JsonResponse
    {

        $user = Auth::user();
        $query = $user->objects();
        try {
            $data = [
                'all' => $query->clone()->count(),
                'progress' => $query->clone()->where('object_status_id', ObjectStatusEnum::PROGRESS)->count(),
                'frozen' => $query->clone()->where('object_status_id', ObjectStatusEnum::FROZEN)->count(),
                'suspended' => $query->clone()->where('object_status_id', ObjectStatusEnum::SUSPENDED)->count(),
                'submitted' => $query->clone()->where('object_status_id', ObjectStatusEnum::SUBMITTED)->count(),
            ];
            return $this->sendSuccess($data, 'Object count retrieved successfully.');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function payment(): JsonResponse
    {
        try {
            $object = Article::query()->with('paymentLogs')->findOrFail(request('object_id'));

            $paid = $object->paymentLogs()
                ->get()
                ->sum(function ($log) {
                    return $log->content->additionalInfo->amount ?? 0;
                });

            $cost = (float)$object->price_supervision_service - (request('amount') + $paid);



            $meta = ['amount' => request('amount'), 'cost' => $cost];

            $tableId = $this->historyService->createHistory(
                guId: $object->id,
                status: $object->object_status_id->value,
                type: LogType::TASK_HISTORY,
                date: null,
                comment: $item['comment'] ?? "",
                additionalInfo: $meta
            );

            $log = ArticlePaymentLog::query()->findOrFail($tableId);

            if (request()->hasFile('file'))
            {
                $file = request()->file('file');
                $path = $file->store('document/payment-log', 'public');
                $log->documents()->create(['url' => $path]);
            }

            if (request()->hasFile('image'))
            {
                $file = request()->file('image');
                $path = $file->store('images/payment-log', 'public');
                $log->images()->create(['url' => $path]);
            }

            return $this->sendSuccess([], 'Article retrieved successfully.');

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function changeObjectStatus(): JsonResponse
    {
        try {

            Article::findOrFail(request('object_id'))->update(['object_status_id' => request('status')]);
            return $this->sendSuccess(null, 'Object status updated');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }
}
