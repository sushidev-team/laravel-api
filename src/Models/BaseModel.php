<?php

namespace AMBERSIVE\Api\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use AMBERSIVE\Api\Traits\Uuids;


class BaseModel extends Model
{

    use Uuids;

    protected static function boot()
    {
        parent::boot();	
	}

    protected $guard_name = 'api';

    /**
     * Mandatory to make the UUID work
     */
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected function getClassName($model){
        $path = explode('\\', get_class($model));
        return array_pop($path);
    }
    
}
