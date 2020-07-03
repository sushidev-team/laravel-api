<?php 

namespace AMBERSIVE\Api\Seeder;

use Log;

use AMBERSIVE\Api\Classes\SeederHelper;
use AMBERSIVE\Api\Helper\SchemaHelper;

class PermissionSeeder extends SeederHelper
{

    protected $schemas = [
        'users',
        'permissions',
        'roles'
    ];

    protected $permissions = [];

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $permissionClass = config('ambersive-api.models.permission_model');       

        // Add super admin permission

        $permission = $permissionClass::firstOrCreate(['guard_name' => 'api', 'name' => '*']);

        // Add additional permissions

        $schemas = array_merge($this->schemas, SchemaHelper::listSchemas());

        foreach ($schemas as $index => $schema) {

            $schemaContent = SchemaHelper::readSchema($schema);
            $schemaFound   = $schemaContent != null;

            // Fallback to
            if ($schemaContent == null) {
                $schemaContent = SchemaHelper::readSchema($schema, dirname(__DIR__)."/Schemas");
            }

            if ($schemaContent !== null) {
                $this->schemas[$schema] = [
                    'fromApp' => $schemaFound,
                    'content' => $schemaContent
                ];
            }
            else {
                Log::warning("Cannot find the schema file for \"${schema}\".");
            }

        }

        $this->generatePermissionsList();

        if (sizeOf($this->permissions) === 0) {
            Log::info("There are no permissions to seed.");
            return;
        }

        // Delete 
        $permissisonsToDelete = $permissionClass::whereNotIn('name', $this->permissions)->where('name', 'not like', "custom-%")->where('name','!=','*')->where('guard_name', 'api')->get();
        $amount = $permissisonsToDelete->count();

        $permissisonsToDelete->each(function($permission){
            $permission->delete();
        });

        Log::info("Permissions deleted: ${amount}.");

        // Update or create permissions
        collect($this->permissions)->each(function($permissionName) use ($permissionClass){

             $permission = $permissionClass::firstOrCreate(['guard_name' => 'api', 'name' => $permissionName]);

        });

        $amount = collect($this->permissions)->count();

        Log::info("Permissions seeded: ${amount}.");

    }
    
    /**
     * This method will generate a list of permissions based on the schema files
     *
     * @return void
     */
    public function generatePermissionsList():void {

        $permissions = [];

        foreach ($this->schemas as $schema){

            $permissions = array_merge($permissions, data_get($schema, 'content.permissions', []));

        }

        $permissions[] = '*';

        $this->permissions = array_unique($permissions);

    }

}
