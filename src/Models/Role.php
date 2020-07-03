<?php

namespace AMBERSIVE\Api\Models;

use Illuminate\Database\Eloquent\Model;

use Spatie\Permission\Models\Role as BaseRole;
use Spatie\Permission\Models\Permission  as BasePermission;

use Spatie\Permission\Contracts\Role as RoleContract;

use AMBERSIVE\Api\Traits\Uuids;

class Role extends BaseRole implements RoleContract
{

    /**
    * @OA\Schema(schema="Role", required={"name","guard_name"},
    *      title="Model: Role",
    *      @OA\Property(
    *          property="id",
    *          type="uuid",
    *          example="358a7456-c8c1-4071-99b5-92bde821b13a",
    *      ),
    *      @OA\Property(
    *          property="name",
    *          type="string",
    *          example="Admin",
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
