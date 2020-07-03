<?php 

namespace AMBERSIVE\Api\Seeder;

use AMBERSIVE\Api\Classes\SeederHelper;

class RoleSeeder extends SeederHelper
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $roleClass = config('ambersive-api.models.role_model');
        $permissionClass = config('ambersive-api.models.permission_model');  

        $roles = $this->loadYaml('roles');
        $permissionsAvailable = $permissionClass::where('guard_name','api')->get()->pluck('name')->toArray();

        if (empty($roles) || isset($roles['entries']) === false) {
            return;
        }

        foreach ($roles['entries'] as $uuid => $roleItem){

            $role = $roleClass::where('id', $uuid)->first();

            $roleItem['id'] = $uuid;


            if ($role === null) {
                $role = $roleClass::create([
                    'id'         => $uuid,
                    'name'       => data_get($roleItem, 'name'),
                    'guard_name' => data_get($roleItem, 'guard_name', 'api'),
                ]);
            }

            $role->name = data_get($roleItem, 'name', $uuid);
            $role->guard_name = data_get($roleItem, 'guard_name', 'api');

            $permissions = collect(data_get($roleItem, 'permissions', []))->filter(function($item) use ($permissionsAvailable){
                if (in_array($item, $permissionsAvailable)){
                    return $item;
                }
            });
            
            $role->givePermissionTo(data_get($roleItem, 'permissions', []));

            $role->save();

        }

        // Superadmin role

        $role = $roleClass::where('id', "28583255-c490-451f-8eee-6e0be6145ab1")->first();

        if ($role === null) {

            $role = $roleClass::create([
                'id'         => "28583255-c490-451f-8eee-6e0be6145ab1",
                "guard_name" => 'api',
                "name"       => 'Admin'
            ]);

        }

        $permission = $permissionClass::where('name','*')->first();

        if ($permission === null){
            return;
        }

        $role->givePermissionTo(['*']);
        
    }
}
