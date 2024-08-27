<?php

namespace App\Http\Controllers\Api;


use App\Http\Requests\UserEditRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResourceCollection;
use App\Http\Resources\UserStatusResource;
use App\Models\User;
use App\Models\UserStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;
use Spatie\Permission\Models\Role;

class UserController extends BaseController
{
    public function users(): JsonResponse
    {
        $query = User::query()
            ->when(request('search'), function ($query) {
                $query->searchByFullName(request('search'))
                    ->searchByPinfOrPhone(request('search'));
            })
            ->when(request('region_id'), function ($query) {
                $query->where('region_id', request('region_id'));
            })
            ->when(request('district_id'), function ($query) {
                $query->where('district_id', request('district_id'));
            })
            ->when(request('role_id'), function ($query) {
                $query->where('role_id', request('role_id'));
            })
            ->when(request('status'), function ($query) {
                $query->where('user_status_id', request('status'));
            })->paginate(\request('perPage', 10));

        return $this->sendSuccess(new UserResourceCollection($query, []), 'All Users', pagination($query));
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
        $user->client_type_id = $request->input('client_type_id');
        $user->client_type_id = $request->input('client_type_id');
        $user->login = $request->input('login');
        $user->password = bcrypt($request->input('password'));
        $user->user_status_id = $request->input('user_status_id');

        if ($request->hasFile('diplom')) {
            $file = $request->file('diplom');
            $fileName = md5(microtime(true)) . '.' . $request->diplom->getClientOriginalExtension();
            $path = public_path() . '/upload/user/diplom';
            $file->move($path, $fileName);
            $user->diplom = $fileName;
        }
        if ($request->hasFile('objective')) {
            $file = $request->file('objective');
            $fileName = md5(microtime(true)) . '.' . $request->objective->getClientOriginalExtension();
            $path = public_path() . '/upload/user/objective';
            $file->move($path, $fileName);
            $user->objective = $fileName;
        }

        $user->save();
        $user->assignRole($role->name);

        return $this->sendSuccess($user, 'User Created Successfully');
    }

    public function edit(): JsonResponse
    {

    }

    public function status(): JsonResponse
    {
        return $this->sendSuccess(UserStatusResource::collection(UserStatus::all()), 'All User status');
    }
}
