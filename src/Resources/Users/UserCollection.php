<?php

namespace AMBERSIVE\Api\Resources\Users;

use Illuminate\Http\Resources\Json\ResourceCollection;

use Auth;
use DB;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Auth\User as Authenticatable;

#region [CUSTOM:IMPORTS]
#endregion [CUSTOM:IMPORTS]

class UserCollection extends ResourceCollection
{
    #region [CUSTOM:TRAITS]
    #endregion [CUSTOM:TRAITS]

    /*
    |
    |--------------------------------------------------------------------------
    | Generated Collection                                                   
    | Please be aware when you run the command "php artisan api:update"      
    | Cause it will automatically update this file                           
    | <LOCKED>: false                                                   
    |--------------------------------------------------------------------------
    |
    */

    public function toArray($request = null)
    {
        $collection = $this->collection->transform(function ($item) {
            $resourceItem = new \AMBERSIVE\Api\Resources\Users\UserResource($item);

            #region [CUSTOM:RESOURCEHANDLER]
            #endregion [CUSTOM:RESOURCEHANDLER]

            return $resourceItem;
        });

        #region [CUSTOM:COLLECTIONHANDLER]
        #endregion [CUSTOM:COLLECTIONHANDLER]
        return $collection;
    }

    #region [CUSTOM:METHODS]
    #endregion [CUSTOM:METHODS]
}
