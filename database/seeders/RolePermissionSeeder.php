<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleAdmin = Role::create(['name' => 'admin']);
        $roleRegister = Role::create(['name' => 'register']);
        $roleInspector = Role::create(['name' => 'inspector']);
        $permissions = [
            [
                'group_name' => 'dashboard',
                //dashboard permissions
                'permissions' =>[
                    'dashboard.view',
                    'dashboard.edit',
                ]
            ],

            [
                'group_name' => 'blog',
                //blog permissions
                'permissions' =>[
                    'blog.create',
                    'blog.view',
                    'blog.edit',
                    'blog.delete',
                    'blog.approve',
                ]
            ],

            [
                'group_name' => 'admin',
                //admin permissions
                'permissions' =>[
                    'admin.create',
                    'admin.view',
                    'admin.edit',
                    'admin.delete',
                    'admin.approve',
                ]
            ],

            [
                'group_name' => 'role',
                //role permissions
                'permissions' =>[
                    'role.create',
                    'role.view',
                    'role.edit',
                    'role.delete',
                    'role.approve',
                ]
            ],

            [
                'group_name' => 'profile',
                //profile permissions
                'permissions' =>[
                    'profile.view',
                    'profile.edit',
                ]
            ],
        ];

        //create permissions and assign role

        for ($i=0; $i<count($permissions); $i++){

            $permissionGroup = $permissions[$i]['group_name'];

            for ($j=0; $j<count($permissions[$i]['permissions']); $j++){
                $permission = Permission::create(['name' => $permissions[$i]['permissions'][$j], 'group_name'=>$permissionGroup]);
                $roleAdmin->givePermissionTo($permission);
                $permission->assignRole($roleAdmin);

                $roleRegister->givePermissionTo($permission);
                $permission->assignRole($roleRegister);

                $roleInspector->givePermissionTo($permission);
                $permission->assignRole($roleInspector);
            }
        }
    }
}
