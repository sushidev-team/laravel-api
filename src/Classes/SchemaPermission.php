<?php

namespace AMBERSIVE\Api\Classes;

use AMBERSIVE\Api\Classes\SchemaBase;

class SchemaPermission extends SchemaBase {

    public $name;
    public $description;

    public function __construct(array $params = [])
    {
        parent::__construct($params);

    }

}