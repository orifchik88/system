<?php

namespace App\Http\Controllers\Api;
use App\Enums\DxaResponseStatusEnum;
use App\Http\Requests\DxaResponseInspectorRequest;
use App\Http\Requests\DxaResponseRegisterRequest;
use App\Http\Requests\DxaResponseRejectRequest;
use App\Http\Resources\DxaResponseResource;
use App\Http\Resources\DxaStatusResource;
use App\Models\DxaResponse;
use App\Models\DxaResponseStatus;
use App\Services\DxaResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class RegisterController extends BaseController
{
    public function __construct(
        protected DxaResponseService $service
    ){}

    public function registers(): JsonResponse
    {

        if (request('status_id'))
        {
            $registers = DxaResponse::query()
                ->where('dxa_response_status_id', DxaResponseStatusEnum::ARCHIVE)
                ->paginate(request()->get('per_page', 10));
        }else{
            $registers = DxaResponse::query()
                ->where('dxa_response_status_id', '!=', DxaResponseStatusEnum::ARCHIVE)
                ->paginate(request()->get('per_page', 10));
        }

        return $this->sendSuccess(DxaResponseResource::collection($registers), 'All registers  successfully.', pagination($registers));
    }

    public function getRegister($id): JsonResponse
    {
        try {
            $register = DxaResponse::query()->findOrFail($id);
            return $this->sendSuccess(DxaResponseResource::make($register), 'Register successfully.');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function status(): JsonResponse
    {
        if (request()->get('id'))
        {
            $register = DxaResponseStatus::findOrFail(request()->get('id'));
            return $this->sendSuccess(DxaStatusResource::make($register), 'Register successfully.');
        }
        $statuses = DxaResponseStatus::query()->get();
        return $this->sendSuccess(DxaStatusResource::collection($statuses), 'All registers status  successfully.');
    }

    public function getPDF(): JsonResponse
    {
        try {
            $response = Http::withBasicAuth(
                config('app.mygov.login'),
                config('app.mygov.password'),
            )->get(config('app.mygov.url').'/get-pdf?id=' . request()->get('id'));

            return $this->sendSuccess($response->json(), 'PDF file generated successfully.');

        }catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function sendInspector(DxaResponseInspectorRequest $request): JsonResponse
    {
        try {
            $this->service->data = [
                'task_id' => $request->post('task_id'),
                'inspector_id' => $request->post('inspector_id')
            ];

            $response = $this->service->sendInspector();

            return $this->sendSuccess(DxaResponseResource::make($response), 'Inspector sent successfully.');

        }catch (\Exception $exception) {
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
            ];

            if ($request->hasFile('images')) {
                $this->service->data['images'] = $request->file('images');
            }

            $response = $this->service->sendRegister();

            return $this->sendSuccess(DxaResponseResource::make($response), 'Register successfully.');
        }catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function rejectRegister(DxaResponseRejectRequest $request): JsonResponse
    {
        try {
            $this->service->data = [
                'task_id' => $request->post('task_id'),
                'reject_comment' => $request->post('reject_comment'),
            ];

            $response = $this->service->sendReject();

            return $this->sendSuccess(DxaResponseResource::make($response), 'Register successfully.');
        }catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }
}
