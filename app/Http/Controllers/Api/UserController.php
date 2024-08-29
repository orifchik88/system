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
use Illuminate\Support\Facades\Request;
use Spatie\Permission\Models\Role;

class UserController extends BaseController
{
    public function __construct(protected UserService $service){}

    public function users(): JsonResponse
    {
        return $this->sendSuccess(new UserResourceCollection($this->service->getAllUsers(), []), 'All Users', pagination($this->service->getAllUsers()));
    }

    public function create(UserRequest $request): JsonResponse
    {

        $role = Role::query()->findOrFail($request->input('role_id'));

        $user = new User();
        $user->pinfl = $request->input('pinfl');
        $user->surname = $request->input('surname');
        $user->name = $request->input('name');
        $user->middle_name = $request->input('middle_name');
        $user->address = $request->input('address');
        $user->passport_number = $request->input('passport_number');
        $user->phone = $request->input('phone');
        $user->region_id = $request->input('region_id');
        $user->district_id = $request->input('district_id');
        $user->login = $request->input('login');
        $user->password = bcrypt($request->input('password'));
        $user->user_status_id = $request->input('user_status_id');


        $user->save();
        $user->assignRole($role->name);

        return $this->sendSuccess($user, 'User Created Successfully');
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
