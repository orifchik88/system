<?php

namespace App\Http\Controllers\Api;


use App\Enums\ObjectStatusEnum;
use App\Enums\RegulationStatusEnum;
use App\Enums\UserHistoryStatusEnum;
use App\Enums\UserHistoryTypeEnum;
use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use App\Http\Requests\PinflRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserHistoryResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResourceCollection;
use App\Http\Resources\UserStatusResource;
use App\Models\Article;
use App\Models\ArticleUser;
use App\Models\Regulation;
use App\Models\Role;
use App\Models\User;
use App\Models\UserHistory;
use App\Models\UserRole;
use App\Models\UserStatus;
use App\Notifications\InspectorNotification;
use App\Services\HistoryService;
use App\Services\MessageTemplate;
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
    )
    {
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

    public function delete(): JsonResponse
    {
        try {
            $user = User::query()->findOrFail(request('id'));
            $objects = $user->objects()->where('object_status_id', '!=', ObjectStatusEnum::SUBMITTED);

            if ($objects->count() > 0)  throw new \Exception('Userda faol obyektlari mavjud');

            $user->update([
                'active' => 0,
                'user_status_id' => UserStatusEnum::RELEASED,
                'deleted_at' => now(),
            ]);
            return $this->sendSuccess(null, 'User has been deleted');
        }catch (\Exception $exception){
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
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
            if ($data['old_user_id'] == $data['new_user_id']) throw new \Exception('Bu foydalanuvchi biriktirilgan');

            $meta = [
                'old_user_id' => $data['old_user_id'],
                'old_role_id' => $data['old_role_id'],
                'new_user_id' => $data['new_user_id'],
                'new_role_id' => $data['new_role_id'],
                'object_id' => $data['object_id'],
            ];

            if ($data['is_inspector']) {
                ArticleUser::query()->where('article_id', $data['object_id'])
                    ->where('role_id', UserRoleEnum::INSPECTOR->value)
                    ->update(['user_id' => $data['new_user_id']]);

                 Regulation::query()
                     ->where('object_id', $data['object_id'])
                     ->where('user_id', $data['old_user_id'])
                     ->where('role_id', UserRoleEnum::INSPECTOR->value)
                    ->update([
                        'user_id' => $data['new_user_id'],
                        'role_id' => $data['new_role_id'],
                    ]);

                Regulation::query()
                    ->where('object_id', $data['object_id'])
                    ->where('created_by_user_id', $data['old_user_id'])
                    ->where('created_by_role_id', UserRoleEnum::INSPECTOR->value)
                    ->update([
                        'created_by_user_id' => $data['new_user_id'],
                        'created_by_role_id' => $data['new_role_id'],
                    ]);

                $this->sendNotification($this->user, $data['new_user_id'], $this->roleId, $data['object_id']);

            } else {
                $meta['inspector_id'] = $data['inspector_id'] ?? null;
                $this->historyService->createHistory(
                    guId: $data['object_id'],
                    status: UserHistoryStatusEnum::ASKED->value,
                    type: UserHistoryTypeEnum::CHANGE->value,
                    date: null,
                    comment: $item['comment'] ?? "",
                    additionalInfo: $meta
                );

                $this->sendNotificationAuthor($this->user, $this->roleId, $data['new_role_id'], $data['object_id']);
            }

            return $this->sendSuccess([], 'Send Successfully');

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function userChangeList(): JsonResponse
    {
        try {
            $userHistories = UserHistory::query()
                ->where('type', UserHistoryTypeEnum::CHANGE->value)
                ->where('content->additionalInfo->inspector_id', auth()->id())
                ->paginate(request('per_page', 10));
            return $this->sendSuccess(UserHistoryResource::collection($userHistories), 'All User History', pagination($userHistories));
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function acceptUserChange(): JsonResponse
    {
        DB::beginTransaction();
        try {
            $userHistory = UserHistory::query()->findOrFail(request('id'));

            $userHistory->update([
                'content->status' => UserHistoryStatusEnum::ACCEPTED->value,
                'content->comment' => request('comment') ?? 'Qabul qilindi',
            ]);

            $objectId = $userHistory->content->additionalInfo->object_id;
            $oldUserId = $userHistory->content->additionalInfo->old_user_id;
            $oldRoleId = $userHistory->content->additionalInfo->old_role_id;
            $newUserId = $userHistory->content->additionalInfo->new_user_id;
            $newRoleId = $userHistory->content->additionalInfo->new_role_id;


            ArticleUser::query()->where('article_id', $objectId)
                ->where('role_id', $oldRoleId)
                ->where('user_id', $oldUserId)
                ->update(['user_id' => $newUserId]);

            Regulation::query()
                ->where('object_id', $objectId)
                ->where('user_id', $oldUserId)
                ->where('role_id', $oldRoleId)
                ->update([
                    'user_id' => $newUserId,
                    'role_id' => $newRoleId,
                ]);

            Regulation::query()
                ->where('object_id', $objectId)
                ->where('created_by_user_id', $oldUserId)
                ->where('created_by_role_id', $oldRoleId)
                ->update([
                    'created_by_user_id' => $newUserId,
                    'created_by_role_id' => $newRoleId,
                ]);
            DB::commit();
            return $this->sendSuccess([], 'Success');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function rejectUserChange(): JsonResponse
    {
        try {
            $userHistory = UserHistory::query()->findOrFail(request('id'));
            $userHistory->update([
                'content->status' => UserHistoryStatusEnum::REJECTED->value,
                'content->comment' => request('comment') ?? 'Rad etildi',
            ]);
            return $this->sendSuccess([], 'Success');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function create(UserRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $existingUser = $this->findUserByPinfl($request->pinfl);

            if ($existingUser) {
                if ($existingUser->user_status_id != UserStatusEnum::RELEASED) {
                    throw new \Exception('Bu foydalanuvchi oldin qo\'shilgan.');
                }

                $this->updateExistingUser($existingUser, $request);
                $this->updateUserRoles($existingUser, $request->role_ids);
                $this->saveFiles($existingUser, $request);

                DB::commit();
                return $this->sendSuccess(new UserResource($existingUser), 'Foydalanuvchi tahrir qilindi.');
            }

            $user = $this->createNewUser($request);
            $this->updateUserRoles($user, $request->role_ids);
            $this->saveFiles($user, $request);

            DB::commit();
            return $this->sendSuccess(new UserResource($user), 'Foydalanuvchi muvaffaqiyatli yaratildi.');

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendError($exception->getMessage(), $exception->getLine());
        }
    }

    private function saveFiles(User $user, UserRequest $request)
    {
        if ($request->hasFile('files')) {
            $user->documents()->delete();
            foreach ($request->file('files') as $file) {
                $path = $file->store('user/docs', 'public');
                $user->documents()->create(['url' => $path]);
            }
        }
    }
    private function findUserByPinfl($pinfl): ?User
    {
        return User::query()->where('pinfl', $pinfl)->first();
    }

    private function updateExistingUser(User $user, UserRequest $request): void
    {
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->surname = $request->surname;
        $user->middle_name = $request->middle_name;
        $user->region_id = $request->region_id;
        $user->district_id = $request->district_id;
        $user->active = 1;
        $user->login = $request->phone;
        $user->password = Hash::make($request->phone);
        $user->user_status_id = UserStatusEnum::ACTIVE;
        $user->type = $request->type;
        $user->created_by = $request->created_by;
        $user->image = $this->saveImage($request);

        $user->save();
    }

    private function createNewUser(UserRequest $request): User
    {
        $user = new User();
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

        $user->image = $this->saveImage($request);

        $user->save();

        return $user;
    }

    private function updateUserRoles(User $user, array $roleIds): void
    {
        UserRole::query()->where('user_id', $user->id)->delete();

        foreach ($roleIds as $role_id) {
            UserRole::query()->create([
                'user_id' => $user->id,
                'role_id' => $role_id
            ]);
        }
    }
    private function saveImage(UserRequest $request): ?string
    {
        if ($request->hasFile('image')) {
            return $request->file('image')->store('user', 'public');
        }

        return null;
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
        } catch (\Exception $exception) {
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

        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function getEmployees(): JsonResponse
    {
        try {
            $user = Auth::user();
            return $this->sendSuccess(UserResource::collection($user->employees), 'All Employees');
        } catch (\Exception $exception) {
            return $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    private function sendNotification($user, $inspectorId, $roleId, $objectId)
    {
        try {
            $role = Role::query()->find($roleId);
            $inspector = User::query()->find($inspectorId);
            $object = Article::query()->find($objectId);
            $message = MessageTemplate::createdObject($user->full_name, $object->task_id, $role->name, now());
            $data = [
                'screen' => 'objects'
            ];
            $inspector->notify(new InspectorNotification(title: "Yangi obyekt biriktirildi", message: $message, url: null, additionalInfo: $data));

        } catch (\Exception $exception) {

        }

    }
    private function sendNotificationAuthor($user, $roleId, $newRoleId, $objectId)
    {
        try {
            $role = Role::query()->find($roleId);
            $newRole = Role::query()->find($newRoleId);
            $object = Article::query()->find($objectId);
            $inspector = $object->users()->where('role_id', UserRoleEnum::INSPECTOR->value)->first();
            $message = MessageTemplate::changeUserInObject($user->full_name, $newRole->name, $object->task_id, $role->name, now());
            $data = [
                'screen' => 'employee'
            ];
            $inspector->notify(new InspectorNotification(title: "Xodim o'zgartirilmoqda", message: $message, url: null, additionalInfo: $data));

        } catch (\Exception $exception) {

        }

    }


    public function status(): JsonResponse
    {
        return $this->sendSuccess(UserStatusResource::collection(UserStatus::all()), 'All User status');
    }
}
