<?php

namespace App\Http\Controllers\Api;


use App\Http\Requests\PinflRequest;
use App\Http\Requests\UserEditRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResourceCollection;
use App\Http\Resources\UserStatusResource;
use App\Models\User;
use App\Models\UserStatus;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseController
{
    public function __construct(protected UserService $service){}

    public function users(): JsonResponse
    {
        return $this->sendSuccess(new UserResourceCollection($this->service->getAllUsers(), []), 'All Users', pagination($this->service->getAllUsers()));
    }

    public function create(UserRequest $request): JsonResponse
    {
        $imagePath = null;

        if ($request->hasFile('image'))
        {
            $imagePath = $request->file('image')->store('user', 'public');
        }

       $user = User::query()->create([
           "name" => $request->name,
           "phone" => $request->phone,
           "pinfl" => $request->pinfl,
           'password' => Hash::make($request->phone),
           'login' => $request->phone,
           "user_status_id" => $request->user_status_id,
           "surname" => $request->surname,
           "middle_name" => $request->middle_name,
           "region_id" => $request->region_id,
           "district_id" => $request->district_id,
           'image' => $imagePath,
       ]);

        if ($request->filled('role_ids')) {
            $user->roles()->attach($request->role_ids);
        }

        return $this->sendSuccess(new UserResource($user),  'User Created Successfully');
    }

    public function edit(): JsonResponse
    {

    }

    public function getPassportInfo(PinflRequest $request): JsonResponse
    {
        try {
           $data = $this->service->getInfo($request->pinfl, $request->birth_date);
           $meta = [
               'pinfl' => $data['current_pinpp'],
               'name' => $data['namelat'],
               'surname' => $data['surnamelat'],
               'middle_name' => $data['patronymlat'],
               'image' => $data['photo'],
           ];
           return $this->sendSuccess($meta, 'Passport Information Get Successfully');

        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function status(): JsonResponse
    {
        return $this->sendSuccess(UserStatusResource::collection(UserStatus::all()), 'All User status');
    }
}
