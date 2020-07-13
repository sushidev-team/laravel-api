<?php 

namespace App\Models\Test;

use AMBERSIVE\Api\Models\BaseModel;

use Auth;use DB;

#region [CUSTOM:IMPORTS]

#endregion [CUSTOM:IMPORTS]

use AMBERSIVE\Api\Traits\Uuids;
use AMBERSIVE\Api\Traits\Encryptable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Test extends BaseModel 
{

    use Uuids;
    use Encryptable;

    

    #region [CUSTOM:TRAITS]
    
    #endregion [CUSTOM:TRAITS]

    /*
    |
    |--------------------------------------------------------------------------
    | Generated Model                                                         
    | Please be aware when you run the command "php artisan api:update"       
    | Cause it will automatically update this file                            
    | <LOCKED>: false                                                    
    |--------------------------------------------------------------------------
    |
    */

    #region [Documentation]: TestModel
/**
* @OA\Schema(schema="TestModel", required={}, title="Model: Test",
    
* )
**/
#endregion [Documentation]: TestModel

#region [Documentation]: Requestbodies
/**
* @OA\Schema(schema="TestRequestBodyStore", required={}, title="Model: Test [Request-Body: TestRequestBodyStore]",

* )
**/
/**
* @OA\Schema(schema="TestRequestBodyUpdate", required={}, title="Model: Test [Request-Body: TestRequestBodyUpdate]",

* )
**/
#endregion [Documentation]: Requestbodies

    protected $table = 'users';

    /**
    * The attributes that are mass assignable.
    * @var array
    */
    protected $fillable = [
        
    ];

    /**
    * The attributes excluded from the model's JSON form.
    * @var array
    */
    protected $hidden = [
        
    ];

    /**
    * This attributes get added to the list / model
    * @var array
    */
    protected $appends = [
        
    ];

    /**
    * The attributes casted and transformed to real values
    * @var array
    */
    protected $casts = [
        
    ];

     /**
    * The attributes that should be encrypted.
    * @var array
    */
    protected $encryptable = [
        
    ];

    /**
    * The attributes that should be mutated to dates.
    * @var array
    */
    protected $dates = [];

    

    

    #region [CUSTOM:METHODS]
    
    #endregion [CUSTOM:METHODS]

}