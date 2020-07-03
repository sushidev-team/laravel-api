<?php

namespace AMBERSIVE\Api\Controller\Auth;

use Illuminate\Http\Request;

use AMBERSIVE\Api\Classes\EndpointRequest;
use AMBERSIVE\Api\Controller\BaseApiController;

use \Illuminate\Http\JsonResponse;

use AMBERSIVE\Api\Models\UserActivation;

use Auth;
use DB;
use Hash;
use Str;
use Validator;

use Carbon\Carbon;

use AMBERSIVE\Api\Exceptions\EndpointValidationInvalid;

class RegisterController extends BaseApiController
{
        
    
    /**
     * @OA\Post(
     *      path="/api/auth/register",
     *      operationId="register",
     *      tags={"Authentication"},
     *      summary="Registration method",
     *      description="Returns an user resource .",
     *      @OA\RequestBody(
     *          required=false,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"username","email", "password", "password_confirmation"},
     *                  @OA\Property(
     *                      property="username",
     *                      description="Username for account",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="firstname",
     *                      description="Firstname of user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="lastname",
     *                      description="Last name of user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="email",
     *                      description="Email address of the user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      description="Password of the user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="password_confirmation",
     *                      description="The password again to ensure that the user has no typing issues.",
     *                      type="string"
     *                  ),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful registration",
     *          @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="status",
     *                         type="integer"
     *                     ),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         @OA\Schema(ref="#/components/schemas/Resources/Users/UserResource")
     *                     ),
     *                     example={
     *                          "status": 200,
     *                          "language": "de",
     *                          "data": {
     *                              "username": "leganz",
     *                              "firstname":"Manuel",
     *                              "lastname":"Pirker-Ihl",
     *                              "email": "mpi@AMBERSIVE.com",
     *                              "roles":{},
     *                              "permissions":{}
     *                          } 
     *                     }
     *                 )
     *             )
     *       ),
     *       @OA\Response(
     *          response=400,
     *          description="Invalid registration",
     *          @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="status",
     *                         type="integer"
     *                     ),
     *                     @OA\Property(
     *                         property="language",
     *                         type="integer"
     *                     ),
     *                     @OA\Property(
     *                         property="data",
     *                         type="object"
     *                     ),
     *                     example={"status": 400,"language": "de","data": {"message": "Die gewünschte Aktion konnte nicht abgeschlossen werden, da ein Fehler aufgetreten ist.", "code": 4 } }
     *                 )
     *             )
     *       )
     * )
     */

    public function register(Request $request) {

        $allow             = config('ambersive-api.allow_register', true);
        $minLength         = config('ambersive-api.password.minlength', 8);

        $userClass         = config('ambersive-api.models.user_model', \AMBERSIVE\Api\Models\User::class);
        $userResourceClass = config('ambersive-api.models.user_resource', \AMBERSIVE\Api\Models\User::class);
        $userRoles         = config('ambersive-api.users.roles', ['User']);
        $setActiveOnRegistration = config('ambersive-api.automatic_active', false);

        if ($allow === false) {
            return $this->respondBadRequest(__('ambersive-api::register.notallowed'));
        }

        $form = $request->only('username','firstname', 'lastname', 'email', 'password', 'password_confirmation', 'language');

        // Validation
        $validation = Validator::make($form, [
            'username'  => 'required|unique:users',
            'email'     => 'required|email|unique:users',
            'password'  => "required|confirmed|min:${minLength}",
        ]);

        if ($validation->fails()) {
            $errors = $validation->errors()->all();
            throw new EndpointValidationInvalid($validation);
        }

        // Do the database stuff for the user creation
        $response = DB::transaction(function() use ($request, $form, $userClass, $userResourceClass, $userRoles, $setActiveOnRegistration){

            $user = $userClass::create([
                'username'  => data_get($form, 'username', null),
                'firstname' => data_get($form, 'firstname', null),
                'lastname'  => data_get($form, 'lastname',  null),
                'email'     => data_get($form, 'email'),
                'password'  => bcrypt(data_get($form, 'password')),
                'language'  => data_get($form, 'language', $request->header('language') != null  ? $request->header('language') : config('app.fallback_locale'))
            ]);

            if ($user === null) {
                return $this->respondBadRequest(__('ambersive-api::register.failed'));
            }

            if ($setActiveOnRegistration === true) {
                $user->active = $setActiveOnRegistration;
            }
            else {
                $userActivation = UserActivation::create(['user_id' => $user->id, 'code' => Str::random(40)]);
                event(new \AMBERSIVE\Api\Events\Registered($user, $userActivation->code));
            }

            $user->syncRoles($userRoles);

            return $this->respondSuccess(new $userResourceClass($user->toArray()));

        });

        return $response != null ? $response : $this->respondBadRequest(__('ambersive-api::register.failed'));

    }

    /**
     * @OA\Get(
     *      path="/api/auth/activation/{code}",
     *      operationId="AccountActivation",
     *      tags={"Authentication"},
     *      summary="Activate an account",
     *      description="This endpoint will allow to activate an account. If the request contains a 'Accept' with a none json content. This endpoint will trigger an redirect.",
     *      security={{"bearer":{}}},
     *      @OA\Parameter(
     *         name="code",
     *         in="path",
     *         description="Activation code.",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *      ),
     *      @OA\Response(
     *           response=200,
     *           description="successful api request.",
     *           @OA\JsonContent(
     *                 allOf={
     *                     @OA\Schema(ref="#/components/schemas/StandardApiResponse"),
     *                     @OA\Schema(
     *                          @OA\Property(
     *                              property="data",
     *                              type="array",
     *                              @OA\Items(ref="#/components/schemas/AccountActivationResource"),
     *                              @OA\Link(link="AccountActivationResource", ref="#/components/links/AccountActivationResource")
     *                          ),
     *                     )
     *                 }
     *           )
     *      )
     *   )
     **/

    /**
    * @OA\Schema(schema="AccountActivationResource", required={"filename","path"},
    *      title="AccountActivationResource",
    *      @OA\Property(
    *          property="message",
    *          type="string",
    *          example="Account has been activated!",
    *      ),
    * )
    **/

    public function activation(Request $request, $code) {

        $userActivation = UserActivation::where('used', false)->where('code', $code)->with('user')->first();
        $requestWantsJson = $request->wantsJson() || $request->header('Accept') === '*/*';

        $errorPage = config('ambersive-api.activation_redirect_failure', '/');

        if ($userActivation == null) {
            return $requestWantsJson == true  ? $this->respondBadRequest(__('ambersive-api::register.activation.failed')): redirect($errorPage);
        }
        else if ($userActivation->user->locked === true) {
            return $requestWantsJson == true  ? $this->respondBadRequest(__('ambersive-api::register.activation.locked')) : redirect($errorPage);
        }
        else if ($userActivation->user->email_verified_at !== null) {
            return $requestWantsJson == true  ? $this->respondBadRequest(__('ambersive-api::register.activation.alreadyverified')): redirect($errorPage);
        }
        else if ($userActivation->used === true) {
            return $requestWantsJson == true  ? $this->respondBadRequest(__('ambersive-api::register.activation.alreadyused')) : redirect($errorPage);
        }

        if ($userActivation->user !== null) {

            // Activate the user account
            $userActivation->user->active = true;
            $userActivation->user->save();
            
            // Set code to be already used
            $userActivation->used = true;
            $userActivation->save();

            return $requestWantsJson == true ? $this->respondSuccess(__('ambersive-api::register.activation.success')) : redirect(config('ambersive-api.activation_redirect_success', '/'));

        }

        return $this->respondBadRequest(__('ambersive-api::register.activation.failed'));

    }

}