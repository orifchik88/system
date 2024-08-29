<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserStatusEnum;
use App\Http\Requests\UserTypeRequest;
use App\Http\Resources\UserTypeResource;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;


class UserTypeController extends BaseController
{
    public function index(): JsonResponse
    {
        try {
            $query = UserType::query()
                ->when(request('s'), function ($query) {
                    $query->whereRaw("type_name LIKE '%" . \request('s') . "%'");
                })
                ->when(request('sort'), function ($query) {
                    $query->orderBy('id', request('sort'));
            })->paginate(\request('perPage', 10));

            return $this->sendSuccess(UserTypeResource::collection($query), 'All users types', UserType::pagination($query));
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function count(): JsonResponse
    {
        try {
            $data = [
                'all' => User::all()->count(),
                'active' => User::query()->where('user_status_id', UserStatusEnum::ACTIVE)->count(),
                'on_holiday' => User::query()->where('user_status_id', UserStatusEnum::ON_HOLIDAY)->count(),
                'released' => User::query()->where('user_status_id', UserStatusEnum::RELEASED)->count(),
            ];
            return $this->sendSuccess($data, 'All users count');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function create(UserTypeRequest $request): JsonResponse
    {
        try {
            $userType = new UserType();
            $userType->fill($request->validated());
            $userType->save();
            return $this->sendSuccess(UserTypeResource::make($userType), 'User type created');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }

    }

    public function edit($id, UserTypeRequest $request): JsonResponse
    {
        try {
            $userType = UserType::findOrFail($id);
            $userType->fill($request->validated());
            $userType->save();
            return $this->sendSuccess(UserTypeResource::make($userType), 'User type updated');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }
}
