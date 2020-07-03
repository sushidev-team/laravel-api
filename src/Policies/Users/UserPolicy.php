<?php

namespace AMBERSIVE\Api\Policies\Users;

use Auth;
use DB;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Auth\User as Authenticatable;

/** CUSTOM:IMPORTS:START **/

/** CUSTOM:IMPORTS:STOP **/

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Collection;

use AMBERSIVE\Api\Models\User as User;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Check if the user has special permissions
     *
     * @return boolean
     */
    public function before($user, $ability)
    {
        #region [CUSTOM:POLICY-BEFORE]
        #endregion [CUSTOM:POLICY-BEFORE]
    }

    public function all(
        User $user,
        \AMBERSIVE\Api\Models\User $first,
        Collection $collection = null
    ): bool {
        $allow = true;
        #region [CUSTOM:POLICY-ALL]
        #endregion [CUSTOM:POLICY-ALL]
        return $allow;
    }

    public function view(User $user, \AMBERSIVE\Api\Models\User $model): bool
    {
        $allow = true;
        #region [CUSTOM:POLICY-SHOW]
        #endregion [CUSTOM:POLICY-SHOW]
        return $allow;
    }

    public function store(User $user): bool
    {
        $allow = true;
        #region [CUSTOM:POLICY-STORE]
        #endregion [CUSTOM:POLICY-STORE]
        return $allow;
    }

    public function update(User $user, \AMBERSIVE\Api\Models\User $model): bool
    {
        $allow = true;
        #region [CUSTOM:POLICY-UPDATE]
        #endregion [CUSTOM:POLICY-UPDATE]
        return $allow;
    }

    public function destroy(User $user, \AMBERSIVE\Api\Models\User $model): bool
    {
        $allow = true;
        #region [CUSTOM:POLICY-DESTROY]
        #endregion [CUSTOM:POLICY-DESTROY]
        return $allow;
    }

    #region [CUSTOM:METHODS]
    #endregion [CUSTOM:METHODS]
}
