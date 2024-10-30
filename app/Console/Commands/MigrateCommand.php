<?php

namespace App\Console\Commands;

use App\Enums\UserRoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\District;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;
use App\Models\UserEmployee;
use App\Models\UserRole;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->migrateUsers();
    }

    private function migrateUsers()
    {
        $users = DB::connection('second_pgsql')->table('user')
            ->where('is_migrated', false)
            ->where('active', 1)
            ->limit(20)->get();

        $userStatuses = [
            '30165510-1d9d-4d6e-bcc5-6246af0cbc22' => UserStatusEnum::ACTIVE,
            'cb4ab3d0-0721-4d24-a459-12aefaebb12b' => UserStatusEnum::ON_HOLIDAY,
            'c67af18e-c131-4bfe-9aa5-1444b6003fd5' => UserStatusEnum::ON_HOLIDAY,
            '31356f88-8c96-4abb-9e68-9410d6fe4223' => UserStatusEnum::ON_HOLIDAY,
            '7418c1ac-6d28-44ab-8e30-7125125fda88' => UserStatusEnum::ON_HOLIDAY,
            'f386319c-7717-402e-9529-815c4cc95c8f' => UserStatusEnum::RELEASED
        ];

        $inspectorRoles = [
            '80b740c4-79ef-4c45-a76a-926f90fa3780' => UserRoleEnum::INSPECTOR,
            '2316d2ab-ae0b-497b-9175-642b414c1886' => UserRoleEnum::INSPECTOR,
            'db60cbca-8d2f-4911-9fdc-6fc30102c669' => UserRoleEnum::INSPECTOR
        ];

        foreach ($users as $userO) {
            $userStatus = isset($userStatuses[$userO->status_id]) ? $userStatuses[$userO->status_id] : UserStatusEnum::ACTIVE;
            $region = Region::query()->where('old_id', $userO->region_id)->first();
            $district = District::query()->where('old_id', $userO->district_id)->first();
            $role = Role::query()->where('old_id', $userO->role_id)->first();
            $checkUser = User::query()->where('login', $userO->login)->first();

            if ($checkUser != null)
                continue;

            if ($role == null && !isset($inspectorRoles[$userO->role_id]))
                continue;

            $role_id = isset($inspectorRoles[$userO->role_id]) ? $inspectorRoles[$userO->role_id] : $role->getAttributes()['id'];

            $user = new User();
            $user->name = $userO->name;
            $user->phone = $userO->phone;
            $user->surname = $userO->surname;
            $user->middle_name = $userO->middle_name;
            $user->region_id = ($region != null) ? $region->id : null;
            $user->district_id = ($district != null) ? $district->id : null;
            $user->pinfl = ($userO->pinfl != null) ? $userO->pinfl : $userO->stir_org;
            $user->password = Hash::make($userO->phone);
            $user->active = 1;
            $user->login = $userO->login;
            $user->password = Hash::make($userO->phone);
            $user->user_status_id = $userStatus;
            $user->save();


            UserRole::query()->create([
                'user_id' => $user->id,
                'role_id' => $role_id
            ]);

            DB::connection('second_pgsql')->table('user')
                ->where('id', $userO->id)
                ->update(
                    [
                        'is_migrated' => true
                    ]
                );
        }
    }
}
