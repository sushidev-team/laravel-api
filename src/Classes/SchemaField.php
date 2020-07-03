<?php

namespace AMBERSIVE\Api\Classes;

use AMBERSIVE\Api\Classes\SchemaBase;

class SchemaField extends SchemaBase {

    public $type;
    public $description;
    public $example;
    public $required_create         = false;
    public $required_update         = false;
    public $encrypt                 = false;
    public $hidden                  = false;

    public function __construct(array $params = [])
    {
        parent::__construct($params);

    }

}