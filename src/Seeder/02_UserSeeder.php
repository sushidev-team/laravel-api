<?php 

namespace AMBERSIVE\Api\Seeder;

use AMBERSIVE\Api\Classes\SeederHelper;

class UserSeeder extends SeederHelper
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $userClass = config('ambersive-api.models.user_model');
        
        $users = $this->loadYaml('users');

        $forceUpdate = false;

        if (empty($users) || isset($users['entries']) === false) {
            return;
        }

        foreach ($users['entries'] as $uuid => $userItem){
            
            $user = $userClass::where('id', $uuid)->withTrashed()->first();

            $userItem['id'] = $uuid;

            if ($user === null) {
                $user = $userClass::create([
                    'id'        => $uuid,
                    'username'  => data_get($userItem, 'username', $uuid),
                    'firstname' => data_get($userItem, 'firstname'),
                    'lastname'  => data_get($userItem, 'lastname'),
                    'email'     => data_get($userItem, 'email'),
                    'password'  => data_get($userItem, 'password', bcrypt(data_get($userItem, 'email')))
                ]);                

            }

            if ($user !== null) {

                $user->deleted_at = null;
                
                if (data_get($userItem, 'password_raw') !== null) {

                    $user->password          = bcrypt(data_get($userItem, 'password_raw'));                    
                    unset($userItem['password_raw']);
                    $userItem['password'] = $user->password;

                    // Overwrite the current user information
                    $users['entries'][$uuid] = $userItem;

                    // Mark to save entries
                    $forceUpdate = true;
                }

                $user->email_verified_at = now();   
                                
                foreach($user->getFillable() as $index => $field) {

                    if (data_get($userItem, $field, null)) {
                        $user->{$field} = data_get($userItem, $field, null);
                    }

                }

                $user->save();        
                
                $user->syncRoles(data_get($userItem, 'roles', ['User']));
                $user->syncPermissions(data_get($userItem, 'permissions', []));

            }

        }

        if ($forceUpdate === true) {
            $this->updateYaml('users', $users);
        }

    }
}
