<?php

namespace AMBERSIVE\Api\Resources\Users;

use Illuminate\Http\Resources\Json\JsonResource;

use Auth;
use DB;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Auth\User as Authenticatable;

#region [CUSTOM:IMPORTS]
#endregion [CUSTOM:IMPORTS]

class UserResource extends JsonResource
{
    #region [CUSTOM:TRAITS]
    #endregion [CUSTOM:TRAITS]

    /*
    |
    |--------------------------------------------------------------------------
    | Generated Resource                                                     
    | Please be aware when you run the command "php artisan api:update"      
    | Cause it will automatically update this file                           
    | <LOCKED>: false                                                   
    |--------------------------------------------------------------------------
    |
    */

    #region [Documentation]: UserResource
    /**
    * @OA\Schema(schema="UserResource", title="Resource: UserResource",
        
    * )
    **/
    #endregion [Documentation]: UserResource

    public function toArray($request)
    {
        $result = parent::toArray($request);

        #region [CUSTOM:RESOURCEHANDLER]
        unset($result['deleted_at']);
        unset($result['created_at']);
        unset($result['updated_at']);
        unset($result['email_verified_at']);
        unset($result['loginAttemptTimestamp']);

        if (isset($result['roles'])) {
            $result['roles'] = $this->roles->pluck('name');
        }

        if (isset($result['permissions'])) {
            $result['permissions'] = $this->getAllPermissions()->pluck('name');
        }
        #endregion [CUSTOM:RESOURCEHANDLER]

        return $result;
    }

    #region [CUSTOM:METHODS]
    #endregion [CUSTOM:METHODS]
}
