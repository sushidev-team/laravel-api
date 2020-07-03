<?php

namespace AMBERSIVE\Api\Models;

use AMBERSIVE\Api\Models\BaseModel;

use Auth;
use DB;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;

#region [CUSTOM:IMPORTS]
#endregion [CUSTOM:IMPORTS]

use AMBERSIVE\Api\Traits\Uuids;
use AMBERSIVE\Api\Traits\Encryptable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    use Uuids;
    use Encryptable;

    use SoftDeletes;

    use HasRoles;

    #region [CUSTOM:TRAITS]
    protected $guard_name = 'api';
    protected $keyType    = 'string';

    public $incrementing  = false;
    public $guarded       = ['api', 'web'];
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

    #region [Documentation]: UserModel
    /**
     * @OA\Schema(schema="UserModel", required={}, title="Model: User",
     *      @OA\Property(
     *          property="id",
     *          type="uuid",
     *          example="12ed13f5-7549-4625-a882-73099c4901f1",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="username",
     *          type="string",
     *          example="",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="firstname",
     *          type="string",
     *          example="",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="lastname",
     *          type="string",
     *          example="",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="email",
     *          type="string",
     *          example="",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="email_verified_at",
     *          type="date",
     *          example="2019-08-01 00:00:00",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="password",
     *          type="string",
     *          example="",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="active",
     *          type="boolean",
     *          example=false,
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="locked",
     *          type="boolean",
     *          example=false,
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="loginAttempts",
     *          type="string",
     *          example="",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="loginAttemptTimestamp",
     *          type="date",
     *          example="2019-08-01 00:00:00",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="language",
     *          type="string",
     *          example="",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="created_at",
     *          type="date",
     *          example="2019-08-01 00:00:00",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="updated_at",
     *          type="date",
     *          example="2019-08-01 00:00:00",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="deleted_at",
     *          type="date",
     *          example="2019-08-01 00:00:00",
     *          description="",
     *      )
     * )
     **/
    #endregion [Documentation]: UserModel

    #region [Documentation]: Requestbodies
    /**
     * @OA\Schema(schema="UserRequestBodyStore", required={}, title="Model: User [Request-Body: UserRequestBodyStore]",
     *      @OA\Property(
     *          property="id",
     *          type="uuid",
     *          example="12ed13f5-7549-4625-a882-73099c4901f1",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="username",
     *          type="string",
     *          example="",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="firstname",
     *          type="string",
     *          example="",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="lastname",
     *          type="string",
     *          example="",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="email",
     *          type="string",
     *          example="",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="email_verified_at",
     *          type="date",
     *          example="2019-08-01 00:00:00",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="password",
     *          type="string",
     *          example="",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="active",
     *          type="boolean",
     *          example=false,
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="locked",
     *          type="boolean",
     *          example=false,
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="loginAttempts",
     *          type="string",
     *          example="",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="loginAttemptTimestamp",
     *          type="date",
     *          example="2019-08-01 00:00:00",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="language",
     *          type="string",
     *          example="",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="created_at",
     *          type="date",
     *          example="2019-08-01 00:00:00",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="updated_at",
     *          type="date",
     *          example="2019-08-01 00:00:00",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="deleted_at",
     *          type="date",
     *          example="2019-08-01 00:00:00",
     *          description="",
     *      ),
     * )
     **/
    /**
     * @OA\Schema(schema="UserRequestBodyUpdate", required={}, title="Model: User [Request-Body: UserRequestBodyUpdate]",
     *      @OA\Property(
     *          property="id",
     *          type="uuid",
     *          example="12ed13f5-7549-4625-a882-73099c4901f1",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="username",
     *          type="string",
     *          example="",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="firstname",
     *          type="string",
     *          example="",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="lastname",
     *          type="string",
     *          example="",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="email",
     *          type="string",
     *          example="",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="email_verified_at",
     *          type="date",
     *          example="2019-08-01 00:00:00",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="password",
     *          type="string",
     *          example="",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="active",
     *          type="boolean",
     *          example=false,
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="locked",
     *          type="boolean",
     *          example=false,
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="loginAttempts",
     *          type="string",
     *          example="",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="loginAttemptTimestamp",
     *          type="date",
     *          example="2019-08-01 00:00:00",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="language",
     *          type="string",
     *          example="",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="created_at",
     *          type="date",
     *          example="2019-08-01 00:00:00",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="updated_at",
     *          type="date",
     *          example="2019-08-01 00:00:00",
     *          description="",
     *      ),
     *      @OA\Property(
     *          property="deleted_at",
     *          type="date",
     *          example="2019-08-01 00:00:00",
     *          description="",
     *      ),
     * )
     **/
    #endregion [Documentation]: Requestbodies

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        "id",
        "username",
        "firstname",
        "lastname",
        "email",
        "email_verified_at",
        "password",
        "loginAttempts",
        "loginAttemptTimestamp",
        "active",
        "locked",
        "language",
        "created_at",
        "updated_at",
        "deleted_at"
    ];

    /**
     * The attributes excluded from the model's JSON form.
     * @var array
     */
    protected $hidden = ["loginAttempts","password"];

    /**
     * This attributes get added to the list / model
     * @var array
     */
    protected $appends = [];

    /**
     * The attributes casted and transformed to real values
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'loginAttempts' => 'integer',
        'loginAttemptTimestamp' => 'datetime',
        'active' => 'boolean',
        'locked' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * The attributes that should be encrypted.
     * @var array
     */
    protected $encryptable = [];

    /**
     * The attributes that should be mutated to dates.
     * @var array
     */
    protected $dates = [
        "email_verified_at",
        "loginAttemptTimestamp",
        "created_at",
        "updated_at",
        "deleted_at"
    ];

    #region [CUSTOM:METHODS]

    /**
     * getJWTIdentifier
     *
     * @return void
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'id'          => $this->id,
            'username'    => $this->username,
            'email'       => $this->email,
            'locked'      => $this->locked,
            'active'      => $this->active,
            'permissions' => $this->getAllPermissions()->pluck('name'),
            'roles'       => $this->roles()->pluck('name')
        ];
    }

    public function resetCodes(){
        return $this->hasMany(\AMBERSIVE\Api\Models\PasswordReset::class, "user_id", "id");
    }

    public function unusedResetCodes(){
        return $this->hasMany(\AMBERSIVE\Api\Models\PasswordReset::class, "user_id", "id");
    }

    #endregion [CUSTOM:METHODS]
}
