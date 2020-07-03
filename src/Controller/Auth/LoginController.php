<?php

namespace AMBERSIVE\Api\Controller\Auth;

use Illuminate\Http\Request;

use AMBERSIVE\Api\Classes\EndpointRequest;
use AMBERSIVE\Api\Controller\BaseApiController;

use \Illuminate\Http\JsonResponse;

use Auth;
use DB;
use Hash;
use Validator;

use App\Models\Users\User;

use Carbon\Carbon;

class LoginController extends BaseApiController
{

    /**
    * @OA\Schema(schema="JWTToken", required={},
    *      title="JWT: Token",
    *      @OA\Property(
    *          property="access_token",
    *          type="string",
    *          example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9sb2NhbGhvc3Q6ODg5OVwvYXBpXC9hdXRoXC9sb2dpbiIsImlhdCI6MTU2NTM4NDQyMCwiZXhwIjoxNTY1Mzg4MDIwLCJuYmYiOjE1NjUzODQ0MjAsImp0aSI6IktaWjg4UFNiVm9TRm9aRDciLCJzdWIiOiJiMGEwMTc4Yy02NzI0LTQzNzUtYWUwMy04MmVkYTA3YTc4ZmYiLCJwcnYiOiI0YWMwNWMwZjhhYzA4ZjM2NGNiNGQwM2ZiOGUxZjYzMWZlYzMyMmU4In0.FWOGMEWT66zomotfPslsURa7AjzkTRnpxuaS1ssuk-E",
    *      ),
    *      @OA\Property(
    *          property="token_type",
    *          type="string",
    *          example="bearer",
    *      ),
    *      @OA\Property(
    *          property="expires_in",
    *          type="integer",
    *          example=3600,
    *      ),
    * )
    **/
        
     /**
     * @OA\Post(
     *      path="/api/auth/login",
     *      operationId="login",
     *      tags={"Authentication"},
     *      summary="Login method",
     *      description="Returns the JWT-Token for further authentication.",
     *      @OA\RequestBody(
     *          required=false,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  type="object",
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
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *           response=200,
     *           description="successful login.",
     *           @OA\JsonContent(
     *                 allOf={
     *                     @OA\Schema(ref="#/components/schemas/StandardApiResponse"),
     *                     @OA\Schema(
     *                          @OA\Property(
     *                              property="data",
     *                              type="array",
     *                              @OA\Items(ref="#/components/schemas/JWTToken"),
     *                              @OA\Link(link="JWTToken", ref="#/components/links/JWTToken")
     *                          ),
     *                     )
     *                 }
     *           )
     *      )
     * )
     */
    public function login(Request $request) {

        $credentials = $request->only(['email', 'password']);
        $maxAttemps  = config('ambersive-api.login_attempts', 3);

        // Validation
        $validatedData = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Read the current user 
        $user = $this->getUserModel()::withTrashed()->where('email', $request->email)->first();

        if ($user === null || $user->deleted_at !== null) {
            return $this->respondBadRequest(__('ambersive-api::auth.failed'));
        }

        // Check if user is locked
        if ($user->locked === true) {

            $wait       = config('ambersive-api.login_locked', 5);

            $dt         = Carbon::parse(Carbon::now())->setTimezone(config('app.timezone'));
            $dtCompare  = Carbon::parse($user->loginAttemptTimestamp, config('app.timezone'));

            if ($user->locked === true && $user->loginAttempts === 0) {
                return $this->respondBadRequest(__('ambersive-api::auth.locked'));
            }

            if($dt->diffInMinutes($dtCompare) < $wait){
                $user->loginAttempts++;
                $user->save();

                $remainingWaitTime = $wait - $dt->diffInMinutes($dtCompare);
                return $this->respondBadRequest(trans_choice('ambersive-api::auth.throttle', $remainingWaitTime, ['minutes' => $remainingWaitTime]));
            }
            else if ($user->active === true && $user->locked === true && $user->loginAttempts > 0) {
                $user->locked = false;
            }

        }

        $user->loginAttemptTimestamp = now();
        $user->loginAttempts++;
        $user->save();
        
        $token = Auth::guard()->attempt($credentials);

        if ($token === false) {

            // User has been locked
            if ($user->loginAttempts >= $maxAttemps) {
                $user->locked = true;
                $user->save();
            }
            
            if ($user->locked === true) {
                return $this->respondBadRequest(__('ambersive-api::auth.locked'));
            }

            return $this->respondBadRequest(__('ambersive-api::auth.failed'));
        }

        $user->loginAttempts = 0;
        $user->save();

        if ($user->active === false) {
            return $this->respondBadRequest(__('ambersive-api::auth.failed'));
        }

        if ($user->email_verified_at == null) {
            return $this->respondBadRequest(__('ambersive-api::auth.notVerified'));
        }

        return $this->respondSuccess([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => Auth::guard()->factory()->getTTL() * 60
        ]);
    }

        
    /**
     * @OA\Get(
     *      path="/users/current-refresh",
     *      operationId="UserCurrentRefreshToken",
     *      tags={"Users"},
     *      summary="Request a new access_token",
     *      description="If you are in possession of the access_token you can request a new access_token within the time timeframe. Even if the access_token does not allow you to request something else.",
     *      security={{"bearer":{}}},
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
     *                              @OA\Items(ref="#/components/schemas/JWTToken"),
     *                              @OA\Link(link="JWTToken", ref="#/components/links/JWTToken")
     *                          ),
     *                     )
     *                 }
     *           )
     *      )
     *   )
     **/
    public function refreshToken(Request $request) {
        $guard = Auth::guard();
        return $this->respondSuccess(
            [
                'access_token' => $guard->refresh(),
                'token_type'   => 'bearer',
                'expires_in'   => $guard->factory()->getTTL() * 60
            ]
        );
    }

}