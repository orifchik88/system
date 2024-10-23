<?php

namespace App\Http\Controllers\Api;


use App\Enums\UserHistoryStatusEnum;
use App\Enums\UserHistoryTypeEnum;
use App\Enums\UserRoleEnum;
use App\Http\Requests\PinflRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResourceCollection;
use App\Http\Resources\UserStatusResource;
use App\Models\User;
use App\Models\UserRole;
use App\Models\UserStatus;
use App\Services\HistoryService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends BaseController
{
    private HistoryService $historyService;
    public function __construct(
        protected UserService $service,
    ){
        $this->middleware('auth');
        parent::__construct();
        $this->historyService = new HistoryService('user_histories');
    }

    public function users(): JsonResponse
    {
        $query = $this->service->getAllUsers($this->user, $this->roleId);
        $filters = request()->only(['search', 'region_id', 'district_id', 'status', 'role_id']);
        $users = $this->service->searchByUser($query, $filters)->paginate(request('per_page', 10));
        return $this->sendSuccess(new UserResourceCollection($users), 'All Users', pagination($users));
    }

    public function count(): JsonResponse
    {
        try {
            return $this->sendSuccess($this->service->getCountByUsers($this->user, $this->roleId), 'All users count');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function userChange(): JsonResponse
    {
        try {
           $data = request()->all();
           $meta = [
               'user_id' => $data['user_id'],
               'role_id' => $data['role_id'],
               'changed_user_id' => $data['changed_user_id'],
               'changed_role_id' => $data['changed_role_id'],
           ];

           $this->historyService->createHistory(
                guId: $data['object_id'],
                status: UserHistoryStatusEnum::ASKED->value,
                type: UserHistoryTypeEnum::CHANGE->value,
                date: null,
                comment: $item['comment'] ?? "",
                additionalInfo: $meta
            );
           return $this->sendSuccess([], 'Send Successfully');

        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function create(UserRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
                $imagePath = null;

                if ($request->hasFile('image'))
                {
                    $imagePath = $request->file('image')->store('user', 'public');
                }
                $user =  new User();
                $user->name = $request->name;
                $user->phone = $request->phone;
                $user->pinfl = $request->pinfl;
                $user->password = Hash::make($request->phone);
                $user->login = $request->phone;
                $user->user_status_id = $request->user_status_id;
                $user->surname = $request->surname;
                $user->middle_name = $request->middle_name;
                $user->region_id = $request->region_id;
                $user->district_id = $request->district_id;
                $user->created_by = $request->created_by;
                $user->type = $request->type;
                $user->image = $imagePath;
                $user->save();

            if ($request->filled('role_ids')) {
                foreach ($request->role_ids as $role_id) {
                    $role = UserRole::query()
                        ->where('user_id', $user->id)
                        ->where('role_id', $role_id)->first();
                    if (!$role){
                        UserRole::query()->create([
                            'user_id' => $user->id,
                            'role_id' => $role_id
                        ]);
                    }
                }
            }
            DB::commit();
            return $this->sendSuccess(new UserResource($user),  'User Created Successfully');
        }catch (\Exception $exception){
            DB::rollBack();
            return $this->sendError($exception->getMessage(), $exception->getLine());
        }
    }

    public function getInspector(): JsonResponse
    {
        try {
            $inspectors = User::query()
                ->when(request('region_id'), function ($query, $region_id) {
                    $query->where('region_id', $region_id);
                })
                ->when(request('district_id'), function ($query, $district_id) {
                    $query->where('district_id', $district_id);
                })
                ->whereHas('roles', function ($query) {
                    $query->where('roles.id', UserRoleEnum::INSPECTOR->value);
                })
                ->paginate(request('per_page', 10));
            return $this->sendSuccess(UserResource::collection($inspectors), 'All inspectors', pagination($inspectors));
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
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

    public function getEmployees(): JsonResponse
    {
        try {
            $user = Auth::user();
            return $this->sendSuccess(UserResource::collection($user->employees), 'All Employees');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function status(): JsonResponse
    {
        return $this->sendSuccess(UserStatusResource::collection(UserStatus::all()), 'All User status');
    }
}
