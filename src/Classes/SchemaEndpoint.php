<?php

namespace AMBERSIVE\Api\Classes;

use AMBERSIVE\Api\Classes\SchemaBase;

class SchemaEndpoint extends SchemaBase {

    public $name        = "";
    public $include     = true;

    public $model;
    public $resource;
    public $fields      = ["*"];
    public $permissions = [];
    public $policy;

    public $where       = [];
    public $with        = [];
    public $order       = [];

    // Hooks

    public $hookPre     = [];
    public $hookPost    = [];

    // Validations     

    public $validations = [];

    // Middleware

    public array $middleware  = ["auth:api"];

    // Documentation
    public String $summary     = "";
    public String $description = "";
    public String $route       = "";

    public String $descriptionSuccess;
    public String $descriptionFailed;
    public array  $tags = [];

    public String $responseType;
    public String $responseMode;
    public String $responseSchemaSuccess;

    public array  $requestParams = [];
    public String $requestBody;

    public function __construct(array $params = [])
    {
        parent::__construct($params);

    }

}