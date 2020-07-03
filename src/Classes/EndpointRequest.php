<?php

namespace AMBERSIVE\Api\Classes;

use Auth;
use DB;
use Gate;
use Validator;

use AMBERSIVE\Api\Classes\SchemaEndpoint;

use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

use AMBERSIVE\Api\Helper\CollectionHelper;

use AMBERSIVE\Api\Exceptions\EndpointValidationInvalid;

class EndpointRequest {

    protected $controller;
    protected $model;
    protected $modelName;
    protected $resource;
    protected $collection;
    protected $policy;

    protected $request;
    protected $requestData;

    protected $endpoint;
    protected $data;
    protected $handlerType;
    protected $user;

    public function __construct($controller, $model, $resource, $collection, $policy, $request = null)
    {
        $this->controller   = $controller;
        $this->model        = new $model();
        $this->modelName    = $model;
        $this->resource     = $resource;
        $this->collection   = $collection;
        $this->policy       = $policy;

        $this->request      = $request === null ? request() : $request;

        $this->requestData  = $this->request->all();

        $this->user         = Auth::guard()->user();

        if (config('app.debug') === true) {
            DB::enableQueryLog();
        }

    }

    /**
     * This will return the model of the endpoint
     *
     * @return void
     */
    public function getModel() {
        return $this->modelName;
    }
    
    /**
     * This will return the class of the resource
     *
     * @return void
     */
    public function getResource() {
        return $this->resource;
    }

    /**
     * This will return the class of the collection
     */
    public function getCollection() {
        return $this->collection;
    }

    /**
     * Returns the current databuck
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Returns the current handlerType
     */
    public function getHandlerType() {
        return $this->handlerType;
    }
    
    /**
     * Returns the current requestData
     */
    public function getRequestData() {
        return $this->requestData;
    }
    
    /**
     * Register an endpoint
     *
     * @param  mixed $endpoint
     * @return void
     */
    public function register(array $endpoint = null) {

        if ($endpoint != null) {
            $this->endpoint = $endpoint;
        }
        return $this;
    }

    /**
     * Check the permissions for the current state
     */
    public function checkPermissions(array $permissions = []) {

        $permissions = array_merge($permissions, isset($this->endpoint['permissions']) ?  $this->endpoint['permissions'] : []);

        if (collect($permissions)->count() === 0) {
            return $this;
        }

        $permissions[] = '*';

        // Check if the user is logged in
        if ($this->user === null) {
            abort(401);
        }
        
        if (sizeOf($permissions) > 0 && $this->user->hasAnyPermission($permissions) === false) {
            abort(403);
        }

        return $this;
    }


    /**
     * Check if there is policy and do the policy check 
     * if there is valid policy
     *
     * @param  mixed $name
     * @return void
     */
    public function checkPolicy(string $name = null) {
       
        $user = Auth::user();
        if ($user === null) {
            abort(401);
        }

        $model = $this->modelName;

        if (isset($this->endpoint['policy']) === false) {
            return $this;
        }

        $policy = $name != null ? $name : $this->endpoint['policy'];

        if ($this->model->count() > 1 && $policy !== 'store') {
            $allow = Gate::allows($policy, [$this->model->first(), $this->model->get()]);
        }
        else {
            $allow = Gate::allows($policy, $this->model->first());
        }

        if ($policy === 'all' && $allow === false && $this->model->count() > 1) {
            abort(403);
        }
        else if ($policy !== 'all' && $allow === false) {
            abort(403);
        }

        return $this;
    }
    
    /**
     * This methods checks the validation rules
     *
     * @param  mixed $messages
     * @return void
     */
    public function checkValidation(array $messages = []) {

        $rules = data_get($this->endpoint, 'validations', []);
        $validation = Validator::make($this->requestData, $rules, $messages);

        if ($validation->fails()) {
            $errors = $validation->errors()->all();
            throw new EndpointValidationInvalid($validation);
        }

        return $this;
    }
    
    /**
     * Execute hooks (methods) and return the value into the request data
     *
     * @param  mixed $fn
     * @return void
     */
    public function hooksPre($fn = null) {

        if (is_callable($fn)) {
            $result = $fn($this->request, $this->requestData, $this->user);
            if ($result !== null) {
                $this->requestData = array_merge($this->requestData, $result);
            }
        }

        return $this;
    }

        
    /**
     * Execute hook (methods) after the main action has been done
     *
     * @param  mixed $fn
     * @return void
     */
    public function hooksPost($fn = null) {

        if (is_callable($fn)) {
            $fn($this->request, $this->requestData, $this->model, $this->data, $this->user);
        }

        return $this;
    }
    
    /**
     * Add additional data to the request
     *
     * @param  mixed $data
     * @return void
     */
    public function addRequestData(array $data = []) {
        $this->requestData = array_merge($this->requestData, $data);
        return $this;
    }
        
    /**
     * Load the database entries into the data variable
     *
     * @param  mixed $id
     * @return void
     */
    public function load(string $id = null) {

        // Reset the model information before loading the data into the model
        $this->reset();

        if ($id !== null) {
            $this->model = $this->model->where('id', $id);
        }

        // Where / Filters
        $wheres = collect(data_get($this->endpoint, 'where', []));

        $wheres->each(function($where){
            $where = 'where'.ucfirst($where);
            if (method_exists($this->controller, $where)){
                $this->model->where(function($query) use ($where){
                    $model =  $this->controller->$where($this->request, $query);
                    if ($model !== null) {
                        $this->model = $model;
                    }
                });
            }
        });

        // Withs / joins
        $withs = collect(data_get($this->endpoint, 'with', []));
        $withs->each(function($with){

            $this->model->with($with);

        });

        // Fields

        $fields = data_get($this->endpoint, 'fields', ['*']);

        if (sizeOf($fields) > 0 || in_array('*', $fields) === false) {
            $this->model = $this->model->select($fields);
        }

        // Order data
        collect(data_get($this->endpoint, 'order', []))->each(function($orderDirection,$orderKey){
            $this->model->orderBy($orderKey, $orderDirection);
        });

        // Load the data actual
        if ($id !== null) {

            $this->data = $this->model->first();

            if ($this->data === null) {
                abort(404);
            }

        }
        else {
            $this->data = $this->model->get();
        }

        return $this;
    }
    
    /**
     * Create an entry based on the requestData
     *
     * @return void
     */
    public function store() {

        $result = DB::transaction(function() {

            $entryModel = $this->model;
            $entry      = $entryModel::create($this->requestData);

            return $entry;

        });

        if ($result === false || $result === null) {
            abort(400);
        }

        $this->data = $result;

        return $this;
    }
    
        
    /**
     * Update a loaded entry based on the request data
     *
     * @return void
     */
    public function update() {

        if ($this->data === null) {
            abort(400);
        }

        $result = DB::transaction(function() {

            return $this->data->update($this->requestData);

        });

        if ($result === false || $result === null) {
            abort(400);
        }

        $this->load($this->data->id);

        return $this;
    }
    
    /**
     * Destroy the loaded entry/entries
     *
     * @return void
     */
    public function destroy() {

        $result = DB::transaction(function() {

            if ($this->data === null || method_exists($this->data, 'delete') === false) {
                return false;
            }

            if ($this->model->count() > 1) {
                $this->model->each(function($item){
                    $item->delete();
                });
            }
            else {
                $this->data->delete();
            }

            return true;

        });

        if ($result === false || $result === null) {
            abort(400);
        }

        $this->data = null;

        return $this;
    }
    
    /**
     * Do stuff / save the action state
     *
     * @param  mixed $action
     * @param  mixed $fn
     * @return void
     */
    public function handler(string $action = null, $fn = null) {

        if ($action === null) {
            return $this;
        }

        switch($action) {
            case 'all':
                $this->handlerType = $action;
            break;
            case 'index':
                $this->handlerType = $action;
            break;
            case 'show':
                $this->handlerType = $action;
            break;
            case 'destroy':
                $this->handlerType = $action;
            break;
            case 'store':
                $this->handlerType = $action;
            break;
            case 'update':
                $this->handlerType = $action;
            break;
            case 'custom':

                $this->handlerType = $action;

                if (is_callable($fn)) {
                    $fn($this, $this->model, $this->data);
                }
                
                break;
        }

        return $this;
    }
    
    /**
     * Transform the data output to a desired output format
     *
     * @param  mixed $type
     * @return void
     */
    public function respond(string $type = null) {

        switch($type) {         
            case 'collection':
                $res  = $this->collection;
                $data = new $res($this->data);
                $data = $data->toArray($this->request);
            break;
            case 'resource':
                $res = $this->resource;
                $resData = new $res($this->data);
                $data = $resData->toArray($this->request);
            break;   
            case 'paginate';
            case 'pagination';
            case 'paginated':
                $res  = $this->collection;
                $data = new $res($this->data);
                $data = CollectionHelper::paginate(collect($data->toArray($this->request)), $data->count(), 1);
            break;
            case 'messageDeleted':
                $data = ['message' => __('ambersive-api::api.responses.deleted')];
            break;
            default:
                $data = $this->data;
            break;
        }

        if (is_array($data) === false && $data !== null) {
            $result = $data->toArray();
        }
        else {
            $result = $data;
        }

        return $this->controller->respondSuccess($result);

    }

    /**
     * Reset the model information
     *
     * @return void
     */
    public function reset() {
        
        $model = $this->modelName;

        $this->model = new $model();
        $this->data  = null;
        return $this;
    }

}