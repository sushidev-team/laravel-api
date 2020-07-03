<?php

namespace AMBERSIVE\Api\Controller\Users;

use Illuminate\Http\Request;

use AMBERSIVE\Api\Classes\EndpointRequest;
use AMBERSIVE\Api\Controller\BaseApiController;

use Auth;
use DB;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Auth\User as Authenticatable;

#region [CUSTOM:IMPORTS]
#endregion [CUSTOM:IMPORTS]

class UserController extends BaseApiController
{
    /*
    |
    |--------------------------------------------------------------------------
    | Generated Controller                                                    
    | Please be aware when you run the command "php artisan api:update"       
    | Cause it will automatically update this file                            
    | <LOCKED>: false                                                    
    |--------------------------------------------------------------------------
    |
    */

    public $api;

    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->api = new EndpointRequest(
            $this,
            \AMBERSIVE\Api\Models\User::class,
            \AMBERSIVE\Api\Resources\Users\UserResource::class,
            \AMBERSIVE\Api\Resources\Users\UserCollection::class,
            \AMBERSIVE\Api\Policies\Users\UserPolicy::class
        );

        #region [CUSTOM:CONSTRUCTOR]
        #endregion [CUSTOM:CONSTRUCTOR]
    }

    #region [Documentation]: /api/users/all
    /**
     * @OA\Get(
     *      path="/api/users/all",
     *      operationId="UserAll",
     *      tags={"Users"},
     *      summary="",
     *      description="",
     *      security={{"bearer":{}}},
     *      @OA\Response(
     *           response=200,
     *           description="Successful response",
     *           @OA\JsonContent(
     *               allOf={
     *                   @OA\Schema(ref="#/components/schemas/StandardApiResponse"),
     *                   @OA\Schema(
     *                           @OA\Property(
     *                               property="data",
     *                               type="array",
     *                               @OA\Items(ref="#/components/schemas/UserModel"),
     *                           ),
     *                       ),
     *               }
     *           )
     *      )
     *   )
     **/
    #endregion
    public function all(Request $request)
    {
        $api = $this->api
            ->register([
                "fields" => ["*"],
                "where" => [],
                "with" => [],
                "hookPre" => [],
                "hookPost" => [],
                "permissions" => ["users-all"],
                "policy" => "all",
                "order" => [],
                "validations" => []
            ])
            ->checkPermissions()
            ->hooksPre(function (Request $request, $requestData) {
                #region [CUSTOM:PREHOOK-ALL]

                #endregion [CUSTOM:PREHOOK-ALL]
                return $requestData;
            })
            ->load()
            ->checkPolicy()
            ->hooksPost(function (
                Request $request,
                $requestData,
                $model,
                $modelData
            ) {
                #region [CUSTOM:POSTHOOK-ALL]
                #endregion [CUSTOM:POSTHOOK-ALL]
            })
            ->handler('all');

        #region [CUSTOM:ENDPOINT-ALL]

        #endregion [CUSTOM:ENDPOINT-ALL]

        return $api->respond('collection');
    }

    #region [Documentation]: /api/users
    /**
     * @OA\Get(
     *      path="/api/users",
     *      operationId="UserIndex",
     *      tags={"Users"},
     *      summary="",
     *      description="",
     *      security={{"bearer":{}}},
     *      @OA\Response(
     *           response=200,
     *           description="Successful response",
     *           @OA\JsonContent(
     *               allOf={
     *                   @OA\Schema(ref="#/components/schemas/StandardApiResponsePaginated"),
     *                   @OA\Schema(
     *                       @OA\Property(
     *                          property="data",
     *                          type="array",
     *                          @OA\Items(ref="#/components/schemas/UserModel"),
     *                       ),
     *                       @OA\Property(
     *                           property="first_page_url",
     *                           type="string",
     *                           example="http://localhost/index?page=1"
     *                       ),
     *                       @OA\Property(
     *                           property="last_page_url",
     *                           type="string",
     *                           example="http://localhost/index?page=1"
     *                       ),
     *                       @OA\Property(
     *                           property="next_page_url",
     *                           type="string",
     *                           example="http://localhost/index?page=1"
     *                       ),
     *                       @OA\Property(
     *                           property="path",
     *                           type="string",
     *                           example="http://localhost/index?page=1"
     *                       ),
     *                       @OA\Property(
     *                           property="prev_page_url",
     *                           type="string",
     *                           example="http://localhost/index?page=1"
     *                       )
     *                   ),
     *               }
     *           )
     *      )
     *   )
     **/
    #endregion
    public function index(Request $request)
    {
        $api = $this->api
            ->register([
                "fields" => ["*"],
                "where" => [],
                "with" => [],
                "hookPre" => [],
                "hookPost" => [],
                "permissions" => ["users-index"],
                "policy" => "all",
                "order" => [],
                "validations" => []
            ])
            ->checkPermissions()
            ->hooksPre(function (Request $request, $requestData) {
                #region [CUSTOM:PREHOOK-INDEX]

                #endregion [CUSTOM:PREHOOK-INDEX]

                return $requestData;
            })
            ->load()
            ->checkPolicy()
            ->hooksPost(function (
                Request $request,
                $requestData,
                $model,
                $modelData
            ) {
                #region [CUSTOM:POSTHOOK-INDEX]
                #endregion [CUSTOM:POSTHOOK-INDEX]
            })
            ->handler('index');

        #region [CUSTOM:ENDPOINT-INDEX]

        #region [CUSTOM:ENDPOINT-INDEX]

        return $api->respond('paginated');
    }

    #region [Documentation]: /api/users/{id}
    /**
     * @OA\Get(
     *      path="/api/users/{id}",
     *      operationId="UserShow",
     *      tags={"Users"},
     *      summary="",
     *      description="",
     *      security={{"bearer":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="12ed13f5-7549-4625-a882-73099c4901f1"
     *         )
     *      ),
     *      @OA\Response(
     *           response=200,
     *           description="Successful response",
     *           @OA\JsonContent(
     *               allOf={
     *                   @OA\Schema(ref="#/components/schemas/StandardApiResponse"),
     *                   @OA\Schema(
     *                           @OA\Property(
     *                               property="data",
     *                               type="object",
     *                               ref="#/components/schemas/UserModel",
     *                           ),
     *                       ),
     *               }
     *           )
     *      )
     *   )
     **/
    #endregion
    public function show(Request $request, $id)
    {
        $api = $this->api
            ->register([
                "fields" => ["*"],
                "where" => [],
                "with" => ['roles'],
                "hookPre" => [],
                "hookPost" => [],
                "permissions" => ["users-show"],
                "policy" => "view",
                "order" => [],
                "validations" => []
            ])
            ->checkPermissions()
            ->hooksPre(function (Request $request, $requestData) {
                #region [CUSTOM:PREHOOK-SHOW]

                #endregion [CUSTOM:PREHOOK-SHOW]

                return $requestData;
            })
            ->load($id)
            ->checkPolicy()
            ->hooksPost(function (
                Request $request,
                $requestData,
                $model,
                $modelData
            ) {
                #region [CUSTOM:POSTHOOK-SHOW]
                #endregion [CUSTOM:POSTHOOK-SHOW]
            })
            ->handler('show');

        #region [CUSTOM:ENDPOINT-SHOW]

        #endregion [CUSTOM:ENDPOINT-SHOW]

        return $api->respond('resource');
    }

    #region [Documentation]: /api/users/{id}
    /**
     * @OA\Put(
     *      path="/api/users/{id}",
     *      operationId="UserUpdate",
     *      tags={"Users"},
     *      summary="",
     *      description="",
     *      security={{"bearer":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="12ed13f5-7549-4625-a882-73099c4901f1"
     *         )
     *      ),
     *      @OA\RequestBody(
     *          required=false,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  type="object",
     *                  ref="#/components/schemas/UserRequestBodyUpdate"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *           response=200,
     *           description="Successful response",
     *           @OA\JsonContent(
     *               allOf={
     *                   @OA\Schema(ref="#/components/schemas/StandardApiResponse"),
     *                   @OA\Schema(
     *                           @OA\Property(
     *                               property="data",
     *                               type="object",
     *                               ref="#/components/schemas/UserModel",
     *                           ),
     *                       ),
     *               }
     *           )
     *      )
     *   )
     **/
    #endregion
    public function update(Request $request, $id)
    {
        $api = $this->api
            ->register([
                "fields" => ["*"],
                "where" => [],
                "with" => [],
                "hookPre" => [],
                "hookPost" => [],
                "permissions" => ["users-update"],
                "policy" => "update",
                "order" => [],
                "validations" => []
            ])
            ->checkPermissions()
            ->hooksPre(function (Request $request, $requestData, $user) {
                #region [CUSTOM:PREHOOK-UPDATE]

                if (isset($requestData['password']) ) {
                    $requestData['password'] = bcrypt($requestData['password']);
                }

                #endregion [CUSTOM:PREHOOK-UPDATE]

                return $requestData;
            })
            ->load($id)
            ->checkPolicy()
            ->update()
            ->hooksPost(function (
                Request $request,
                $requestData,
                $model,
                $modelData,
                $user
            ) {
                #region [CUSTOM:POSTHOOK-UPDATE]

                if (isset($requestData['roles']) && $user->hasAnyPermission(['*', 'users-roles'])){
                    $modelData->syncRoles($requestData['roles']);
                }

                if (isset($requestData['permisisons']) && $user->hasAnyPermission(['*', 'users-permissions'])){
                    $modelData->syncPermissions($requestData['permissions']);
                }

                #endregion [CUSTOM:POSTHOOK-UPDATE]
            })
            ->handler('update');

        #region [CUSTOM:ENDPOINT-UPDATE]

        #endregion [CUSTOM:ENDPOINT-UPDATE]

        return $api->respond('resource');
    }

    #region [Documentation]: /api/users
    /**
     * @OA\Post(
     *      path="/api/users",
     *      operationId="UserStore",
     *      tags={"Users"},
     *      summary="",
     *      description="",
     *      security={{"bearer":{}}},
     *      @OA\RequestBody(
     *          required=false,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  type="object",
     *                  ref="#/components/schemas/UserRequestBodyStore"
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *           response=200,
     *           description="Successful response",
     *           @OA\JsonContent(
     *               allOf={
     *                   @OA\Schema(ref="#/components/schemas/StandardApiResponse"),
     *                   @OA\Schema(
     *                           @OA\Property(
     *                               property="data",
     *                               type="object",
     *                               ref="#/components/schemas/UserModel",
     *                           ),
     *                       ),
     *               }
     *           )
     *      )
     *   )
     **/
    #endregion
    public function store(Request $request)
    {
        $api = $this->api
            ->register([
                "fields" => ["*"],
                "where" => [],
                "with" => [],
                "hookPre" => [],
                "hookPost" => [],
                "permissions" => ["users-store"],
                "policy" => "store",
                "order" => [],
                "validations" => []
            ])
            ->checkPermissions()
            ->hooksPre(function (Request $request, $requestData) {

                #region [CUSTOM:PREHOOK-STORE]

                if (isset($requestData['password']) ) {
                    $requestData['password'] = bcrypt($requestData['password']);
                }

                $requestData['email_verified_at'] = now();

                #endregion [CUSTOM:PREHOOK-STORE]

                return $requestData;
            })
            ->checkPolicy()
            ->store()
            ->hooksPost(function (
                Request $request,
                $requestData,
                $model,
                $modelData,
                $user
            ) {
                #region [CUSTOM:POSTHOOK-STORE]

                if (isset($requestData['roles']) && $user->hasAnyPermission(['*', 'users-roles'])){
                    $modelData->syncRoles($requestData['roles']);
                }

                if (isset($requestData['permisisons']) && $user->hasAnyPermission(['*', 'users-permissions'])){
                    $modelData->syncPermissions($requestData['permissions']);
                }

                #endregion [CUSTOM:POSTHOOK-STORE]
            })
            ->handler('store');

        #region [CUSTOM:ENDPOINT-STORE]

        #endregion [CUSTOM:ENDPOINT-STORE]

        return $api->respond('resource');
    }

    #region [Documentation]: /api/users/{id}
    /**
     * @OA\Delete(
     *      path="/api/users/{id}",
     *      operationId="UserDestroy",
     *      tags={"Users"},
     *      summary="",
     *      description="",
     *      security={{"bearer":{}}},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="12ed13f5-7549-4625-a882-73099c4901f1"
     *         )
     *      ),
     *      @OA\Response(
     *           response=200,
     *           description="Successful response",
     *           @OA\JsonContent(
     *               allOf={
     *                   @OA\Schema(ref="#/components/schemas/StandardApiDestroyResponse"),
     *
     *               }
     *           )
     *      )
     *   )
     **/
    #endregion
    public function destroy(Request $request, $id)
    {
        $api = $this->api
            ->register([
                "fields" => ["*"],
                "where" => [],
                "with" => [],
                "hookPre" => [],
                "hookPost" => [],
                "permissions" => ["users-destroy"],
                "policy" => "destroy",
                "order" => [],
                "validations" => []
            ])
            ->checkPermissions()
            ->hooksPre(function (Request $request, $requestData) {
                #region [CUSTOM:PREHOOK-DELETE]

                #endregion [CUSTOM:PREHOOK-DELETE]
                return $requestData;
            })
            ->load($id)
            ->checkPolicy()
            ->destroy()
            ->hooksPost(function (
                Request $request,
                $requestData,
                $model,
                $modelData
            ) {
                #region [CUSTOM:POSTHOOK-DELETE]
                #endregion [CUSTOM:POSTHOOK-DELETE]
            })
            ->handler('destroy');

        #region [CUSTOM:ENDPOINT-DELETE]

        #endregion [CUSTOM:ENDPOINT-DELETE]

        return $api->respond('messageDeleted');
    }

    /*
    |--------------------------------------------------------------------------
    | Pleace insert your custom methods within the custom methods block.      |
    |--------------------------------------------------------------------------
    */

    #region [CUSTOM:METHODS]
    
    /**
     * This endpoint will return the current user object
     *
     * @param  mixed $request
     * @return void
     */
    public function current(Request $request) {

        $user = $this->getCurrentUser();

        if ($user === null) {
            return $this->respondUnauthorized();
        }

        $api = $this->api
            ->register([
                "fields" => ["*"],
                "where" => [],
                "with" => ['permissions', 'roles'],
                "hookPre" => [],
                "hookPost" => [],
                "permissions" => ["users-current"],
                "order" => [],
                "validations" => []
            ])
            ->checkPermissions()
            ->load($user->id)
            ->handler('show');

        return $api->respond('resource');

    }

    public function currentRefresh(Request $request) {

        $guard = Auth::guard();

        return $this->respondSuccess(
            [
                'access_token' => $guard->refresh(),
                'token_type' => 'bearer',
                'expires_in' => $guard->factory()->getTTL() * 60
            ]
        );
    }

    #endregion [CUSTOM:METHODS]
}
