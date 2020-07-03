<?php

namespace AMBERSIVE\Api\Classes;

use AMBERSIVE\Api\Classes\SchemaBase;
use AMBERSIVE\Api\Classes\SchemaRole;
use AMBERSIVE\Api\Classes\SchemaPermission;
use AMBERSIVE\Api\Classes\SchemaEndpoint;

use AMBERSIVE\Api\Helper\SchemaHelper;

use Str;

class SchemaDeclaration extends SchemaBase {

    public $table        = "";
    public $model        = "";
    public $resource     = "";
    public $collection   = "";
    public $policy       = null;
    public $locked       = false;
    public $lockedHard   = false;
    public $fields;  
    public $imports      = [
        'Auth',
        'DB'
    ];
    public $traits    = [];
    public $implement = [];
    public $extends   = "BaseModel";
    public $appends   = [];
    public $casts     = [];
    public $relations = [];    
    public $methods   = [];

    public $roles;
    public $permissions;
    public $endpoints;
    public array $endpoints_exclude = [];
    public array $requestBodies = [];
    public array $schemaResource = [];

    public function __construct(array $params = [])
    {
        parent::__construct($params);

    }

    public function toArray(){
        
        $this->update();

        $data = parent::toArray();

        // Transform the endpoints
        if ($data['endpoints'] !== null){

            $endpoints = [];

            $data['endpoints']->each(function($endpoint) use (&$endpoints){
                $endpoints[$endpoint->name] = $endpoint->toArray();
            });

            $data['endpoints'] = $endpoints;

        }

        // Transform the permissions
        if ($this->permissions !== null) {

            $permissions = [];

            collect($this->permissions)->each(function($permission) use (&$permissions){
                 $permissions[] = $permission->name;
            });

            $data['permissions'] = $permissions;

        }

        // Transform the roles
        if ($this->roles !== null) {

            $roles =  [];

            collect($this->roles)->each(function($role) use (&$roles){
                $roles[$role->name] = [
                    'description' => isset($role->description) ? $role->description : null,
                    'permissions' => $role->permissions
                ];
           });

           $data['roles'] = $roles;

        }

        return $data;
    }
 
    /**
     * This method will set the model with the correct namespace etc.
     *
     * @param  mixed $model
     * @return void
     */
    public function setModel($model = null) {

        if ($model !== null){
            
            $modelNamespace  = str_replace('/', '\\', $model);
            $this->model     = $this->createNamespacePrefixForModel().$modelNamespace;

            $className       = SchemaHelper::extractClassName($this->model);
            $this->policy    = SchemaHelper::transformModelToPolicyNamespace($this->model, $className.'Policy');
        }
        else {
            $this->model = null;
        }

        return $this;
    }

    /**
     * Create a correct resource class namespace
     */
    public function setResource($model = null) {
        $this->createNamespaceCorrectResourceFile('resource', $model);
        return $this;
    }

    /**
     * Create a correct collection class namespace
     */
    public function setCollection($model = null) {
        $this->createNamespaceCorrectResourceFile('collection', $model);
        return $this;
    }

        
    /**
     * Set the default endpoints for this schema
     *
     * @return void
     */
    public function setDefaultEndpoints() {

        $defaultEndpoints = collect([
            new SchemaEndpoint([
                'name'        => 'all',
                'include'     => true,
                'permissions' => [$this->table.'-all'],
                'policy'      => 'all'
            ]),
            new SchemaEndpoint([
                'name'        => 'index',
                'include'     => true,
                'permissions' => [$this->table.'-index'],
                'policy'      => 'all'
            ]),
            new SchemaEndpoint([
                'name'          => 'show',
                'include'       => true,
                'permissions'   => [$this->table.'-show'],
                'policy'        => 'view',
                'requestParams' => ['id']
            ]),
            new SchemaEndpoint([
                'name'          => 'update',
                'include'       => true,
                'permissions'   => [$this->table.'-update'],
                'policy'        => 'update',
                'requestParams' => ['id']
            ]),
            new SchemaEndpoint([
                'name'        => 'store',
                'include'     => true,
                'permissions' => [$this->table.'-store'],
                'policy'      => 'store',
            ]),
            new SchemaEndpoint([
                'name'          => 'destroy',
                'include'       => true,
                'permissions'   => [$this->table.'-destroy'],
                'policy'        => 'destroy',
                'requestParams' => ['id']
            ])
        ])->filter(function($endpoint){
            if (in_array($endpoint->name, $this->endpoints_exclude && is_array($this->endpoints_exclude) ?  $this->endpoints_exclude : []) === false) {
                return $endpoint;
            }
        });

        // Merge the endpoints if already some exists
        if ($this->endpoints !== null) {
            $defaultEndpoints = $defaultEndpoints->merge($this->endpoints);
        }

        $this->endpoints = $defaultEndpoints;

        return $this;
    }

    /**
     * Add a new endpoint to the declaration
     */
    public function addEndpoint(array $params = []){

        $this->endpoints->push(new SchemaEndpoint($params));
        return $this;

    }

    /**
     * Reset the endpoints for a specific declaration
     */
    public function resetEndpoints() {
        $this->endpoints = null;
        return $this;
    }

    /**
     * Update the element
     */
    public function update(array $params = []){

        // Resolve the parent update statement
        parent::update($params);

        if ($this->table !== null && $this->table !== ""){
            
            // Update the permissions for this schema
            $this->updatePermissions();

            // Update the roles
            $this->updateRoles();

        }

        return $this;

    }

    /**
     * Update the permissions array
     */
    public function updatePermissions() {

        $permissions = [];

        if ($this->endpoints !== null && $this->table !== null && $this->table !== ""){

            $base = str_replace("_", "-", $this->table);

            foreach ($this->endpoints as $key => $endpoint){

                $permissions[] = new SchemaPermission([
                    'name' => $base.'-'.$endpoint->name
                ]);

            }

            $this->permissions = $permissions;

        }

        return $this;

    }

    /**
     * Update Roles will create an array of possible Roles
     * This method will add a role with all permisions if there is no other role with the singular
     * name of the table
     */
    public function updateRoles() {

        // Based on the roles automatically create roles based on the name of the 
        // This role wil have the permission for all the endpoint permissions provided

        $name  = ucfirst(Str::singular($this->table));
        $roles = $this->roles;

        if ($roles === null) {
            $roles   = [];
            $role    = new SchemaRole(['name' => $name, 'description' => 'This role will have access to all endpoints of the table:'.$this->table, 'permissions' => ['*']]);
            $roles[] = $role;
        }

        $this->roles = $roles;

        return $this;

    }

    /**
     * This helper function will create the namespace prefix
     * for the model
     */
    public function createNamespacePrefixForModel():string{
        $ns = 'App\\';

        $folder = config('ambersive-api.model_laravel');
        $folderNamespace = str_replace('/', '\\', $folder);

        return $ns.$folderNamespace.'\\';
    }

    /**
     * This is a helper method for the Namespace creation for resource/collection file
     */
    protected function createNamespaceCorrectResourceFile($type = null, $model = null) {

        $ns            = 'App\\Http\\Resources';
        $nsModelPrefix = $this->createNamespacePrefixForModel();

        if ($type == null) {
            return $this;
        }

        $typeMethod = 'set'.ucfirst($type);

        if (method_exists($this, $typeMethod) === false) {
            return $this;
        }

        if ($this->model === null || $this->model === '') {
            return $this->setModel(Str::singular($this->table))->$typeMethod($model != null ? $model : $this->model);
        }

        $model  = $this->model;
        $folder = config("ambersive-api.${type}_laravel");

        if ($folder === null) {
            return $this;
        }

        $folderNamespace = str_replace('/', '\\', $folder);

        $model = str_replace($nsModelPrefix, '', $model);

        $namespaceForFile = str_replace('/', '\\', $model);

        $splitted = explode('\\', $namespaceForFile);

        $splitted[sizeOf($splitted) - 1] = ucfirst($splitted[sizeOf($splitted) - 1]);

        $namespaceForFile = implode('\\', $splitted);

        $this->$type    = $ns.$folderNamespace.'\\'.ucfirst($namespaceForFile).ucfirst($type);

    }

}