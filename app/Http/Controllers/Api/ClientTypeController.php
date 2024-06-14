<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ClientTypeRequest;
use App\Http\Resources\ClientTypeResource;
use App\Models\ClientType;
use App\Models\UserType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;

class ClientTypeController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ClientType::query();
            if ($s = $request::input('s')) {
                $query->whereRaw("type_name LIKE '%" . $s . "%'");
            }
            if ($sort = $request::input('sort')) {
                $query->orderBy('id', $sort);
            }
//            $query = $query->paginate($request::input('perPage', 10));
            $query = $query->get();

            return $this->sendSuccess(ClientTypeResource::collection($query), 'All Client Types');
        }catch (\Exception $exception){
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
        }catch (\Exception $exception){
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
