<?php

namespace AMBERSIVE\Api\Models;

use Auth;
use DB;

use AMBERSIVE\Api\Models\BaseModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserActivation extends BaseModel
{

    /**
    * The database table used by the model.
    * @var string
    */
    protected $table = 'users_activations';

    /**
    * The attributes that are mass assignable.
    * @var array
    */
    protected $fillable = [
        "id",
        "code",
        "used",
        "user_id",
        "created_at",
        "updated_at"
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
        "used"       => "boolean",
        "created_at" => "datetime",
        "updated_at" => "datetime"
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
    protected $dates = ["created_at","updated_at"];

    /**
     * Returns the related user
     */
    public function user(){
        $userClass         = config('ambersive-api.models.user_model', \AMBERSIVE\Api\Models\User::class);
        return $this->belongsTo($userClass, "user_id", "id");
    }

}
