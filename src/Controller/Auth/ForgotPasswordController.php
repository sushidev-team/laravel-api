<?php

namespace AMBERSIVE\Api\Controller\Auth;

use Illuminate\Http\Request;

use AMBERSIVE\Api\Classes\EndpointRequest;
use AMBERSIVE\Api\Controller\BaseApiController;

use \Illuminate\Http\JsonResponse;

use Auth;
use DB;
use Hash;
use Str;
use Validator;

use App\Models\Users\User;

use Carbon\Carbon;

use AMBERSIVE\Api\Exceptions\EndpointValidationInvalid;

class ForgotPasswordController extends BaseApiController
{

    /**
     * @OA\Post(
     *      path="/api/auth/password/forgotten",
     *      operationId="forgot-password",
     *      tags={"Authentication"},
     *      summary="Forgot password",
     *      description="This endpoint will send an reset code for the password to the given email address. It will send this code via email to the provided email addresse. (if user exists) + if user is not locked or inactive.",
     *      @OA\RequestBody(
     *          required=false,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"email"},
     *                  @OA\Property(
     *                      property="email",
     *                      description="Email address of the user",
     *                      type="string"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Responese message. Please be aware that there will be always a positiv answer due to security reasons.",
     *          @OA\JsonContent(
     *                 allOf={
     *                     @OA\Schema(ref="#/components/schemas/StandardApiResponse"),
     *                     @OA\Schema(
     *                          @OA\Property(
     *                              property="data",
     *                              type="array",
     *                              @OA\Items(ref="#/components/schemas/MessageResponse"),
     *                              @OA\Link(link="MessageResponse", ref="#/components/links/MessageResponse")
     *                          ),
     *                     )
     *                 }
     *           )
     *       )
     * )
     */
    public function forgotPassword(Request $request) {

        $email = $request->only('email');

        // Validation
        $validation = Validator::make($request->all(), [
            'email'     => 'required|email'
        ]);

        if ($validation->fails()) {
            $errors = $validation->errors()->all();
            throw new EndpointValidationInvalid($validation);
        }
        
        $userClass         = config('ambersive-api.models.user_model', \AMBERSIVE\Api\Models\User::class);

        $user              = $userClass::where('email', $email)->where('active', true)->where('locked', false)->first();

        if ($user === null) {
            return $this->respondSuccess(__('ambersive-api::forgotpassword.sent'));
        }

        // Create a password reset code
        $forgotPassword = \AMBERSIVE\Api\Models\PasswordReset::create([
            'user_id' => $user->id,
            'code' => Str::random(40)
        ]);

        event(new \AMBERSIVE\Api\Events\ForgotPassword($user, $forgotPassword->code));

        return $this->respondSuccess(__('ambersive-api::forgotpassword.sent'));

    }

    /**
     * @OA\Post(
     *      path="/api/auth/password",
     *      operationId="set-password",
     *      tags={"Authentication"},
     *      summary="Set the password for an account. Use this if you have a valid reset code.",
     *      description="This endpoint allows to change the password for a given user. It require user_id/email and reset-code/old password to be successfully",
     *      @OA\RequestBody(
     *          required=false,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"email","password", "password_confirmation"},
     *                  @OA\Property(
     *                      property="email",
     *                      description="Email address of the user",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="user_id",
     *                      description="User id of the given user. You will need to pass this if no email is passed",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="code",
     *                      description="Reset code. This parameter is required if not the correct old password is passed.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="password_old",
     *                      description="Provide your old password if you do not have a reset code.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="password",
     *                      description="Provide the new password.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="password_confirmation",
     *                      description="This field must be the exact same value like the password field.",
     *                      type="string"
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Responese message. Please be aware that there will be always a positiv answer due to security reasons.",
     *          @OA\JsonContent(
     *                 allOf={
     *                     @OA\Schema(ref="#/components/schemas/StandardApiResponse"),
     *                     @OA\Schema(
     *                          @OA\Property(
     *                              property="data",
     *                              type="array",
     *                              @OA\Items(ref="#/components/schemas/MessageResponse"),
     *                              @OA\Link(link="MessageResponse", ref="#/components/links/MessageResponse")
     *                          ),
     *                     )
     *                 }
     *           )
     *       )
     * )
     */
    public function setPassword(Request $request) {


        // Validation
        $validation = Validator::make($request->all(), [
            'email'        => 'required_without:user_id|email|exists:users,email',
            'user_id'      => 'required_without:email|exists:users,id',
            'password_old' => 'required_without:code',
            'code'         => 'required_without:password_old',
            'password'     => 'required|confirmed' 
        ]);

        if ($validation->fails()) {
            $errors = $validation->errors()->all();
            throw new EndpointValidationInvalid($validation);
        }

        $userModel = config('auth.providers.users.model');

        $user = $userModel::where(function($query) use ($request){

            $query->where('email', $request->input('email'))->orWhere('id', $request->input('user_id'));

        })->with('unusedResetCodes')->first();

        $code = $user->unusedResetCodes->first();
        if ($request->input('password_old') === null && ($code === null || $code->used === true)) {
            return $this->respondBadRequest(__('ambersive-api::users.password.setpassword.used'));
        }
        else if ($request->input('password_old') !== null && Hash::check($request->input('password_old'), $user->password) === false) {
            return $this->respondBadRequest(__('ambersive-api::users.password.setpassword.failed'));
        }

        // Code expired?
        $dateExpireCompare = Carbon::now()->sub(config('ambersive-api.password.reset_expires_minues', 1440), 'minutes');

        if ($code !== null && $dateExpireCompare->isBefore($code->created_at) === false) {
            return $this->respondBadRequest(__('ambersive-api::users.password.setpassword.expired'));
        }

        // Update the password
        $user->password = bcrypt($request->input('password'));
        $user->save();

        return $this->respondSuccess(__('ambersive-api::users.password.setpassword.changed'));

    }

}