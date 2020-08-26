<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use Auth;use DB;

#region [CUSTOM:IMPORTS]

#endregion [CUSTOM:IMPORTS]

class TestResource extends JsonResource
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

    #region [Documentation]: TestResource
/**
* @OA\Schema(schema="TestResource", title="Resource: TestResource",
    *      @OA\Property(
*          property="id",
*          type="string",
*          example={{example}},
*          description="",
*      ),
* )
**/
#endregion [Documentation]: TestResource

    public function toArray($request)
    {
        $result = parent::toArray($request);

        #region [CUSTOM:RESOURCEHANDLER]
        
        #endregion [CUSTOM:RESOURCEHANDLER]
        
        return $result;
    }

    #region [CUSTOM:METHODS]
    
    #endregion [CUSTOM:METHODS]

}

