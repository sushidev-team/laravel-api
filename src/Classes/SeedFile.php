<?php

namespace AMBERSIVE\Api\Classes;

class SeedFile  {

    public $model;
    public $entries = [];

    public function __construct(array $params = [])
    {
        parent::__construct($params);

    }

}