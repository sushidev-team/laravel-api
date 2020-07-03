<?php

namespace AMBERSIVE\Api\Classes;

use AMBERSIVE\Api\Classes\SchemaBase;

class SchemaRelation extends SchemaBase {

    public $name;
    public $field;
    public $field_foreign = "id";
    public $type  = 'belongsTo';
    public $model;
    public $with  = [];
    public $order = ['created_at' => 'DESC'];

    public function __construct(array $params = [])
    {
        parent::__construct($params);

    }

}