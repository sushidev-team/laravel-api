<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

use Auth;use DB;

#region [CUSTOM:IMPORTS]

#endregion [CUSTOM:IMPORTS]

class TestCollection extends ResourceCollection
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
        $collection =  $this->collection->transform(function($item){
            $resourceItem =  new \App\Http\Resources\TestResource($item);

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

