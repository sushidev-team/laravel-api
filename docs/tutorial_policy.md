# Policy files 

By default the api generator creates the following Structure for a policy file.

In the given example we create a policy for the access to the User-Model.

````
<?php

namespace App\Policies\Users;

use Auth;
use DB;

#region [CUSTOM:IMPORTS]

#endregion [CUSTOM:IMPORTS]

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Collection;

use App\Models\Users\User as User;

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
        #region [CUSOTM:POLICY-BEFORE]
        #endregion [CUSOTM:POLICY-BEFORE]
    }

    public function all(
        User $user,
        \App\Models\Users\User $first,
        Collection $collection = null
    ): bool {
        $allow = true;
        #region [CUSOTM:POLICY-ALL]
        #endregion [CUSOTM:POLICY-ALL]
        return $allow;
    }

    public function view(User $user, \App\Models\Users\User $model): bool
    {
        $allow = true;
        #region [CUSOTM:POLICY-SHOW]

        $allow = $model->id === $user->id;

        #endregion [CUSOTM:POLICY-SHOW]
        return $allow;
    }

    #region [CUSOTM:METHODS]
    #region [CUSOTM:METHODS]
}
````

## Methods

By default the following policy methods are created:
- all
- view
- store
- update
- destroy

Every method requires a boolean value as return  value.
The all methods gets as second parameter a collection. All other get a single instance of a actual model from the database.

### Modifications
As you might notice one of the core principals of this package is to provide a basic structure for a program workflow. To modify or extend those flows this package is using comment blocks starting with *CUSTOM*. 

Code blocks within those segments will be persient, even you make update via 'php artisan api:update'.