<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ClientTypeRequest;
use App\Http\Resources\ClientTypeResource;
use App\Models\ClientType;
use App\Models\UserType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class ClientTypeController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        try {
//            $query = ClientType::query()
//                ->when(request('s'), function ($query) {
//                    $query->whereRaw("name LIKE '%" . \request('s') . "%'");
//                })
//                ->when(request('sort'), function ($query) {
//                    $query->orderBy('id', request('sort'));
//                })->paginate(\request('perPage', 10));

//            $query = ClientType::query()->select(['id', 'user_type_id', 'name'])->search(request('s'))
//                ->when(request('sort'), function ($query) {
//                    $query->orderBy('id', request('sort'));
//                })->paginate(\request('perPage', 10));

            $query = ClientType::search(request('s'))
                ->when(request('sort'), function ($query) {
                    $query->orderBy('id', request('sort'));
                })->paginate(\request('perPage', 10));

            return $this->sendSuccess(ClientTypeResource::collection($query), 'All Client Types', pagination($query));
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage());
        }
    }

    public function create(ClientTypeRequest $request): JsonResponse
    {
        try {
            $clientType = new ClientType();
            $clientType->fill($request->validated());
            $clientType->save();
            return $this->sendSuccess(ClientTypeResource::make($clientType), 'Client type created');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function edit($id, ClientTypeRequest $request): JsonResponse
    {
        try {
            $clientType = ClientType::findOrFail($id);
            $clientType->fill($request->validated());
            $clientType->save();
            return $this->sendSuccess(ClientTypeResource::make($clientType), 'Client type updated');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }
}
