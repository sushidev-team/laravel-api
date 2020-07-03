<?php

namespace AMBERSIVE\Api\Models;

use Illuminate\Database\Eloquent\Model;

use AMBERSIVE\Api\Traits\Uuids;

use Spatie\Permission\Models\Permission as BasePermission;
use Spatie\Permission\Contracts\Permission as PermissionContract;

class Permission extends BasePermission implements PermissionContract
{

    /**
    * @OA\Schema(schema="Permission", required={"name","guard_name"},
    *      title="Model: Permission",
    *      @OA\Property(
    *          property="id",
    *          type="uuid",
    *          example="0acea0ac-31b2-459d-9a60-40ce57173ba6",
    *      ),
    *      @OA\Property(
    *          property="name",
    *          type="string",
    *          example="users-all",
    *      ),
    *      @OA\Property(
    *          property="guard_name",
    *          type="string",
    *          example="api",
    *      ),
    *      @OA\Property(
    *          property="created_at",
    *          type="date",
    *          example="2019-08-01 00:00:00",
    *      ),
    *      @OA\Property(
    *          property="updated_at",
    *          type="date",
    *          example="2019-08-01 00:00:00",
    *      )
    * )
    **/

    use Uuids;

    public $guarded       = ['api', 'web'];
    public $incrementing  = false;

    protected $keyType    = 'string';
    
}
