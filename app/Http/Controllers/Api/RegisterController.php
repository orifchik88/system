<?php

namespace App\Http\Controllers\Api;

use App\Enums\DxaResponseStatusEnum;
use App\Enums\LawyerStatusEnum;
use App\Http\Requests\DxaResponseInspectorRequest;
use App\Http\Requests\DxaResponseRegisterRequest;
use App\Http\Requests\DxaResponseRejectRequest;
use App\Http\Resources\DxaResponseResource;
use App\Http\Resources\DxaStatusResource;
use App\Models\Article;
use App\Models\DxaResponse;
use App\Models\DxaResponseStatus;
use App\Services\ArticleService;
use App\Services\CheckListAnswerService;
use App\Services\DxaResponseService;
use App\Services\MonitoringService;
use App\Services\RegulationService;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class RegisterController extends BaseController
{
    public function __construct(
        protected DxaResponseService     $service,
        protected ArticleService         $articleService,
        protected RegulationService      $regulationService,
        protected MonitoringService      $monitoringService,
        protected CheckListAnswerService $checkListService,
    )
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function registers(): JsonResponse
    {
        $query = $this->service->getRegisters($this->user, $this->roleId, 1);
        $filters = request()->only(['customer', 'name', 'status', 'object_type', 'task_id', 'district_id', 'lawyer_status']);
        $registers = $this->service->searchRegisters($query, $filters)
            ->orderBy('created_at', request('sort_by_date', 'DESC'))
            ->paginate(request('per_page', 10));

        return $this->sendSuccess(DxaResponseResource::collection($registers), 'All registers  successfully.', pagination($registers));
    }

    public function reRegister(): JsonResponse
    {
        $query = $this->service->getRegisters($this->user, $this->roleId, 2);
        $filters = request()->only(['customer', 'name', 'status', 'object_type', 'task_id', 'district_id', 'lawyer_status']);
        $registers = $this->service->searchRegisters($query, $filters)
            ->orderBy('created_at', request('sort_by_date', 'DESC'))
            ->paginate(request('per_page', 10));

        return $this->sendSuccess(DxaResponseResource::collection($registers), 'All registers  successfully.', pagination($registers));
    }


    public function totalCount(): JsonResponse
    {
        try {
            $data = [
                'register' => $this->service->getRegisters($this->user, $this->roleId, 1)->count(),
                're_register' => $this->service->getRegisters($this->user, $this->roleId, 2)->count(),
                'petition' => 0,
                'checklist' => $this->checkListService->getChecklists($this->user, $this->roleId, 1)->count(),
                'object' => $this->articleService->getObjects($this->user, $this->roleId)->count(),
                'regulation' => $this->regulationService->getRegulations($this->user, $this->roleId)->count(),
                'monitoring' => $this->monitoringService->getMonitorings($this->user, $this->roleId)->count()
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
           $query = $this->service->getRegisters($this->user, $this->roleId, 1);

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

    public function lawyerCount(): JsonResponse
    {
        try {
            $query = $this->service->getRegisters($this->user, $this->roleId, 1);

            $data = [
                'all' => $query->clone()->count(),
                'new' => $query->clone()->where('lawyer_status_id', LawyerStatusEnum::NEW->value)->count(),
                'process' => $query->clone()->where('lawyer_status_id', LawyerStatusEnum::PROCESS->value)->count(),
                'administrative' => $query->clone()->where('lawyer_status_id', LawyerStatusEnum::ADMINISTRATIVE->value)->count(),
                'disassembly' => $query->clone()->where('lawyer_status_id', LawyerStatusEnum::DISASSEMBLY->value)->count(),
            ];
            return $this->sendSuccess($data, 'Response count retrieved successfully.');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function reRegisterCount(): JsonResponse
    {

        try {
            $query = $this->service->getRegisters($this->user, $this->roleId, 2);
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
