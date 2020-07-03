<?php

namespace AMBERSIVE\Api\Classes;

use AMBERSIVE\Api\Classes\SchemaBase;

class SchemaRole extends SchemaBase {

    public $name;
    public $description;
    public $permissions = [];

    public function __construct(array $params = [])
    {
        parent::__construct($params);

    }

}