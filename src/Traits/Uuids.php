<?php
namespace AMBERSIVE\Api\Traits;

use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\ModelNotFoundException;

trait Uuids
{

    /**
     * Boot function from laravel.
     */
    protected static function bootUuids()
    {
        static::creating(function ($model) {
            if (!$model->{config('uuid.default_uuid_column','id')}) {
                $model->{config('uuid.default_uuid_column','id')} = Uuid::uuid4()->toString();
            }
        });
        static::saving(function ($model) {

            $original_uuid = $model->getOriginal(config('uuid.default_uuid_column','id'));
        
            // To make sure you can set a uuid yourself
            if ($original_uuid === null && $model->{config('uuid.default_uuid_column','id')} !== null && $model->{config('uuid.default_uuid_column')} !== "") {                
                return;
            }

            if ($original_uuid !== $model->{config('uuid.default_uuid_column','id')}) {
                $model->{config('uuid.default_uuid_column','id')} = $original_uuid;
            }

        });
    }

    /**
     * Scope  by uuid 
     * @param  string  uuid of the model.
     * 
    */
    public function scopeUuid($query, $uuid, $first = true)
    {
        $match = preg_match('/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/', $uuid);

        if (!is_string($uuid) || $match !== 1)
        {
            throw (new ModelNotFoundException)->setModel(get_class($this));
        }
    
        $results = $query->where(config('uuid.default_uuid_column','id'), $uuid);
    
        return $first ? $results->firstOrFail() : $results;
    }

}
