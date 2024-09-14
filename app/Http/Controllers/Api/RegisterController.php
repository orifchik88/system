<?php

namespace App\Http\Controllers\Api;

use App\Enums\DxaResponseStatusEnum;
use App\Http\Requests\DxaResponseInspectorRequest;
use App\Http\Requests\DxaResponseRegisterRequest;
use App\Http\Requests\DxaResponseRejectRequest;
use App\Http\Resources\DxaResponseResource;
use App\Http\Resources\DxaStatusResource;
use App\Models\Article;
use App\Models\DxaResponse;
use App\Models\DxaResponseStatus;
use App\Services\DxaResponseService;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class RegisterController extends BaseController
{
    public function __construct(
        protected DxaResponseService $service
    )
    {
    }

    public function registers(): JsonResponse
    {
        $user = Auth::user();

        $registers = DxaResponse::query()
            ->when($user->inspector(), function ($query) use ($user) {
                return $query->where('inspector_id', $user->id);
            })
            ->when($user->register(), function ($query) use ($user) {
                return $query->where('region_id', $user->region_id);
            })
            ->where('notification_type', 1);

        if ($status = request('status')) {
            $registers->where('dxa_response_status_id', $status);
        }

        $registers->when(request('task_id'), fn($query) => $query->searchByTaskId(request('task_id')))
            ->when(request('customer'), fn($query) => $query->searchByCustomer(request('customer')))
            ->when(request('name'), fn($query) => $query->searchByName(request('name')))
            ->when(request('object_type'), fn($query) => $query->where('object_type_id', request('object_type')))
            ->when(request('district_id'), fn($query) => $query->where('district_id', request('district_id')))
            ->when(request('funding_source'), fn($query) => $query->where('funding_source_id', request('funding_source')))
            ->when(request('sort_by_date'), fn($query) => $query->orderBy('created_at', request('sort_by_date', 'desc')))
            ->orderBy('id', 'desc');

        $data = $registers->paginate(request('per_page', 10));

        return $this->sendSuccess(DxaResponseResource::collection($data), 'All registers  successfully.', pagination($data));
    }

    public function reRegister(): JsonResponse
    {
        $user = Auth::user();

        $registers = DxaResponse::query()
            ->when($user->inspector(), function ($query) use ($user) {
                return $query->where('inspector_id', $user->id);
            })
            ->when($user->register(), function ($query) use ($user) {
                return $query->where('region_id', $user->region_id);
            })
            ->where('notification_type', 2);

        if ($status = request('status')) {
            $registers->where('dxa_response_status_id', $status);
        }

        $registers->when(request('task_id'), fn($query) => $query->searchByTaskId(request('task_id')))
            ->when(request('customer'), fn($query) => $query->searchByCustomer(request('customer')))
            ->when(request('name'), fn($query) => $query->searchByName(request('name')))
            ->when(request('object_type'), fn($query) => $query->where('object_type_id', request('object_type')))
            ->when(request('district_id'), fn($query) => $query->where('district_id', request('district_id')))
            ->when(request('funding_source'), fn($query) => $query->where('funding_source_id', request('funding_source')))
            ->when(request('sort_by_date'), fn($query) => $query->orderBy('created_at', request('sort_by_date', 'desc')))
            ->orderBy('id', 'desc');

        $data = $registers->paginate(request('per_page', 10));

        return $this->sendSuccess(DxaResponseResource::collection($data), 'All registers  successfully.', pagination($data));
    }


    public function totalCount(): JsonResponse
    {
        try {
            $user = Auth::user();

            $registerCount = DxaResponse::query()
                ->when($user->inspector(), function ($query) use ($user) {
                    return $query->where('inspector_id', $user->id);
                })
                ->when($user->register(), function ($query) use ($user) {
                    return $query->where('region_id', $user->region_id);
                })
                ->where('notification_type', 1)
                ->whereIn('dxa_response_status_id', [DxaResponseStatusEnum::NEW, DxaResponseStatusEnum::SEND_INSPECTOR, DxaResponseStatusEnum::IN_REGISTER])
                ->count();
            $reRegisterCount = DxaResponse::query()
                ->when($user->inspector(), function ($query) use ($user) {
                    return $query->where('inspector_id', $user->id);
                })
                ->when($user->register(), function ($query) use ($user) {
                    return $query->where('region_id', $user->region_id);
                })
                ->where('notification_type', 2)
                ->whereIn('dxa_response_status_id', [DxaResponseStatusEnum::NEW, DxaResponseStatusEnum::SEND_INSPECTOR, DxaResponseStatusEnum::IN_REGISTER])
                ->count();

            $petitionCount = 0;
            if ($user->register()) {
                $objectCount = Article::query()->where('region_id', $user->region_id)->count();
            } else {
                $objectCount = $user->objects()->count();
            }

            $data = [
                'register' => $registerCount,
                're_register' => $reRegisterCount,
                'petition' => $petitionCount,
                'object' => $objectCount,
            ];

            return $this->sendSuccess($data, 'All data');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage());
        }

    }

    public function getRegister($id): JsonResponse
    {
        try {
            $response = DxaResponse::query()->findOrFail($id);
            return $this->sendSuccess(DxaResponseResource::make($response), 'Register successfully.');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }


    public function status(): JsonResponse
    {
        if (request()->get('id')) {
            $register = DxaResponseStatus::findOrFail(request()->get('id'));
            return $this->sendSuccess(DxaStatusResource::make($register), 'Register successfully.');
        }
        $statuses = DxaResponseStatus::query()->get();
        return $this->sendSuccess(DxaStatusResource::collection($statuses), 'All registers status  successfully.');
    }

    public function getPDF(): JsonResponse
    {
        try {
            if (request('type') == 1) {
                $response = Http::withBasicAuth(
                    config('app.mygov.login'),
                    config('app.mygov.password'),
                )->get(config('app.mygov.linear') . '/get-pdf?id=' . request('id'));
                return $this->sendSuccess($response->json(), 'PDF file generated successfully.');

            }
            if (request('type') == 2) {
                $response = Http::withBasicAuth(
                    config('app.mygov.login'),
                    config('app.mygov.password'),
                )->get(config('app.mygov.url') . '/get-pdf?id=' . request('id'));
                return $this->sendSuccess($response->json(), 'PDF file generated successfully.');
            }
            return $this->sendSuccess(null, 'File not found');


        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function sendInspector(DxaResponseInspectorRequest $request): JsonResponse
    {
        try {
            $this->service->data = [
                'task_id' => $request->post('task_id'),
                'inspector_id' => $request->post('inspector_id'),
                'gnk_id' => $request->post('gnk_id'),
                'funding_source_id' => $request->post('funding_source_id'),
                'sphere_id' => $request->post('sphere_id'),
                'program_id' => $request->post('program_id'),
                'end_term_work' => $request->post('end_term_work'),
            ];

            $response = $this->service->sendInspector();

            return $this->sendSuccess(DxaResponseResource::make($response), 'Inspector sent successfully.');

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }

    }

    public function sendRegister(DxaResponseRegisterRequest $request): JsonResponse
    {
        try {
            $this->service->data = [
                'task_id' => $request->post('task_id'),
                'administrative_status_id' => $request->post('administrative_status_id'),
                'long' => $request->post('long'),
                'lat' => $request->post('lat'),
                'commit' => $request->post('commit'),
                'blocks' => $request->post('blocks')
            ];

            if ($request->hasFile('images')) {
                $this->service->data['images'] = $request->file('images');
            }

//            if ($request->hasFile('documents')){
//                $this->service->data['documents'] = $request->file('documents');
//            }

            $response = $this->service->sendRegister();

            return $this->sendSuccess(DxaResponseResource::make($response), 'Register successfully.');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function sphere(): JsonResponse
    {
        try {
            $client = new Client();
            $apiCredentials = config('app.passport.login') . ':' . config('app.passport.password');

            $resClient = $client->post('https://api.shaffofqurilish.uz/api/v1/request/monitoring-soha',
                [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode($apiCredentials),
                    ]
                ]);

            $response = json_decode($resClient->getBody(), true);

            return $this->sendSuccess($response['result']['data']['data'], 'Sphere successfully.');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function registerCount(): JsonResponse
    {

        try {
            $user = Auth::user();
            $query = DxaResponse::query()
                ->when($user->inspector(), function ($query) use ($user) {
                    return $query->where('inspector_id', $user->id);
                })
                ->when($user->register(), function ($query) use ($user) {
                    return $query->where('region_id', $user->region_id);
                })
                ->where('notification_type', 1);
            $data = [
                'all' => $query->clone()->count(),
                'new' => $query->clone()->where('dxa_response_status_id', DxaResponseStatusEnum::NEW)->count(),
                'in_inspector' => $query->clone()->where('dxa_response_status_id', DxaResponseStatusEnum::SEND_INSPECTOR)->count(),
                'in_register' => $query->clone()->where('dxa_response_status_id', DxaResponseStatusEnum::IN_REGISTER)->count(),
                'accepted' => $query->clone()->where('dxa_response_status_id', DxaResponseStatusEnum::ACCEPTED)->count(),
                'rejected' => $query->clone()->where('dxa_response_status_id', DxaResponseStatusEnum::REJECTED)->count(),
                'cancelled' => $query->clone()->where('dxa_response_status_id', DxaResponseStatusEnum::CANCELED)->count(),
            ];
            return $this->sendSuccess($data, 'Response count retrieved successfully.');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function reRegisterCount(): JsonResponse
    {

        try {
            $user = Auth::user();
            $query = DxaResponse::query()
                ->when($user->inspector(), function ($query) use ($user) {
                    return $query->where('inspector_id', $user->id);
                })
                ->when($user->register(), function ($query) use ($user) {
                    return $query->where('region_id', $user->region_id);
                })
                ->where('notification_type', 2);
            $data = [
                'all' => $query->clone()->count(),
                'new' => $query->clone()->where('dxa_response_status_id', DxaResponseStatusEnum::NEW)->count(),
                'in_inspector' => $query->clone()->where('dxa_response_status_id', DxaResponseStatusEnum::SEND_INSPECTOR)->count(),
                'in_register' => $query->clone()->where('dxa_response_status_id', DxaResponseStatusEnum::IN_REGISTER)->count(),
                'accepted' => $query->clone()->where('dxa_response_status_id', DxaResponseStatusEnum::ACCEPTED)->count(),
                'rejected' => $query->clone()->where('dxa_response_status_id', DxaResponseStatusEnum::REJECTED)->count(),
                'cancelled' => $query->clone()->where('dxa_response_status_id', DxaResponseStatusEnum::CANCELED)->count(),
            ];
            return $this->sendSuccess($data, 'Response count retrieved successfully.');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function rejectRegister(DxaResponseRejectRequest $request): JsonResponse
    {
        try {
            $comment = $request->post('reject_comment');

            $response = DxaResponse::query()->where('task_id', $request->post('task_id'))->first();
            $this->service->sendReject($response, $comment);
            $this->service->sendMyGovReject($response);

            return $this->sendSuccess([], 'Register successfully.');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }
}
