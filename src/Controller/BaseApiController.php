<?php

namespace AMBERSIVE\Api\Controller;

use App;
use Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Routing\Controller as Response;
use \Illuminate\Http\Response as IlluminateResponse;
use \Illuminate\Http\JsonResponse;

class BaseApiController extends Controller
{
    
    protected int $statusCode       = -1;
    protected string $returnFormat  = 'application/json';
    protected bool $download        = false;
    protected bool $gzip            = false;
    protected $user                 = null;

    /**
     * @OA\Schema(schema="DefaultMessageDestroy", required={}, title="StandardApiDestroyResponseMessage",
     *      @OA\Property(
     *          property="message",
     *          type="string",
     *          example="Entry has been deleted successfully!",
     *          description="Message from the server will be returned based on the language",
     *      ),
     * )
     **/

    /**
     * @OA\Schema(schema="StandardApiResponse", required={},
     *      @OA\Property(
     *          property="status",
     *          type="integer",
     *          example=200
     *      ),
     *      @OA\Property(
     *          property="data",
     *          type="object|array"
     *      )
     * )
     * 
     */

    /**
     * @OA\Schema(schema="StandardApiResponsePaginated", required={},
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/StandardApiResponse"),
     *          @OA\Schema(
     *              @OA\Property(
     *                  property="current_page",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @OA\Property(
     *                  property="first_page_url",
     *                  type="string",
     *                  example="http://localhost/api/customers?page=1"
     *              ),
     *              @OA\Property(
     *                  property="from",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @OA\Property(
     *                  property="last_page",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @OA\Property(
     *                  property="last_page_url",
     *                  type="string",
     *                  example="http://localhost/api/customers?page=1"
     *              ),
     *              @OA\Property(
     *                  property="next_page_url",
     *                  type="string",
     *                  example="http://localhost/api/customers?page=1"
     *              ),
     *              @OA\Property(
     *                  property="path",
     *                  type="string",
     *                  example="http://localhost/api/customers?page=1"
     *              ),
     *              @OA\Property(
     *                  property="per_page",
     *                  type="integer",
     *                  example=25
     *              ),
     *              @OA\Property(
     *                  property="prev_page_url",
     *                  type="string",
     *                  example="http://localhost/api/customers?page=1"
     *              ),
     *              @OA\Property(
     *                  property="to",
     *                  type="integer",
     *                  example=1
     *              ),
     *              @OA\Property(
     *                  property="total",
     *                  type="integer",
     *                  example=25
     *              )
     *          )
     *       }
     * )
     * 
     */

    /**
     * @OA\Schema(schema="StandardApiDestroyResponse", required={},
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/StandardApiResponse"),
     *          @OA\Schema(
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 ref="#/components/schemas/DefaultMessageDestroy",
     *              ),
     *          ),
     *       }
     * )
     * 
     */

    public function __construct(Request $request){

        $this->setReturnFormat(optional($request)->header('Accept'));
        // Flags and response variables

        $this->download     = ((optional($request)->input('download') === true) || optional($request)->header('download') === 'true');
        $this->user         = optional($request)->user == null ? $this->getCurrentUser() : optional($request)->user;

    }
    
    /**
     * Returns the class of the user model
     *
     * @return void
     */
    protected function getUserModel() {
        return config('auth.providers.users.model');
    }
    
    /**
     * Returns the current user 
     *
     * @return void
     */
    protected function getCurrentUser() {
        return Auth::guard()->user();
    }

    /**
     * Returns the "response" format for the request
     * @return mixed
     */
    public function getReturnFormat(){
        return $this->returnFormat;
    }

    /**
     * Returns the default status code if none is passed
     */
    public function getDefaultStatusCode():int {
        return IlluminateResponse::HTTP_OK;
    }

    /**
     * Set the status code of the response
     */
    public function setStatus(int $status = null) {
        $this->statusCode = $status != null ? $status : $this->getDefaultStatusCode();
        return $this;
    }

    /**
     * Returns the status code of the response
     */
    public function getStatus():int {
        return $this->statusCode;
    }

    /**
     * Sets the response return format
     * @param mixed $returnFormat
     */
    public function setReturnFormat(string $returnFormat = null)
    {
        if ($returnFormat == null) {
            return $this;
        }

        $this->returnFormat = $returnFormat;
        return $this;
    }

    public function isDownload(){
        if ($this->download === null){
            return false;
        }
        return $this->download;
    }

    /**
     * Respond with data
     */
    public function respond($data, array $headers = []): JsonResponse {

        $gzip     = config('ambersive-api.gzip', false);

        // Deactivate the response gzip for this response
        if (App::environment() === 'development' || App::environment() === 'testing') {
            $gzip = false;
        }

        // Handle gzip for request
        if ($gzip === true) {
            if    (!in_array('ob_gzhandler', ob_list_handlers())) {
                ob_start('ob_gzhandler');
            }
            else  {
                ob_start();
            }
        }

        // Handle if the api response is just a string
        if (is_string($data) == true) {
            $data = ['message' => $data];
        }

        $responseData = [
            'status' => $this->getStatus(),
            'data'   => $data
        ];

        $result = \Response::json($responseData, $this->getStatus(), $headers);

        return $result;
        
    }

    /**
     * @OA\Schema(schema="StandardApiSuccess", required={},
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/StandardApiResponse"),
     *          @OA\Schema(
     *              @OA\Property(
     *                  property="status",
     *                  example=200
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  example={}
     *              ),
     *          )
     *      }
     *  )
     */

    public function respondSuccess($data, array $headers = [], $format = null): JsonResponse{
        return $this->setStatus(IlluminateResponse::HTTP_OK)
                    ->setReturnFormat($format)
                    ->respond($data, $headers);
    }

    /**
     * @OA\Schema(schema="StandardApiBadRequest", required={},
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/StandardApiResponse"),
     *          @OA\Schema(
     *              @OA\Property(
     *                  property="status",
     *                  example=400
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  example={}
     *              ),
     *          )
     *      }
     *  )
     */

    public function respondBadRequest($data = null, array $headers = []): JsonResponse{
        return $this->setStatus(IlluminateResponse::HTTP_BAD_REQUEST)
                    ->respond($data, $headers);
    }

    /**
     * @OA\Schema(schema="StandardApiUnauthorized", required={},
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/StandardApiResponse"),
     *          @OA\Schema(
     *              @OA\Property(
     *                  property="status",
     *                  example=401
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  @OA\Property(
     *                      property="message",
     *                      type="string",
     *                      example="You are not allowed to proceed with this action."
     *                  )
     *              ),
     *          )
     *      }
     *  )
     */

    public function respondUnauthorized($message = null, array $headers = []): JsonResponse{
        return $this->setStatus(IlluminateResponse::HTTP_UNAUTHORIZED)
                    ->respond($message != null ? $message : __('ambersive-api::misc.unauthorized'), $headers);
    }

    /**
     * @OA\Schema(schema="StandardApiForbidden", required={},
     *      allOf={
     *          @OA\Schema(ref="#/components/schemas/StandardApiResponse"),
     *          @OA\Schema(
     *              @OA\Property(
     *                  property="status",
     *                  example=403
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  @OA\Property(
     *                      property="message",
     *                      type="string",
     *                      example="You do not have enough permissions."
     *                  )
     *              ),
     *          )
     *      }
     *  )
     */

    public function respondForbidden($message = null, array $headers = []): JsonResponse{
        return $this->setStatus(IlluminateResponse::HTTP_FORBIDDEN)
                    ->respond($message != null ? $message : __('ambersive-api::misc.forbidden'), $headers);
    }

    /**
    * @OA\Schema(schema="MessageResponse", required={},
    *      title="Message Response",
    *      description="Basic structure of a message response",
    *      @OA\Property(
    *          property="message",
    *          type="string",
    *          example="Text goes here",
    *      ),
    * )
    **/

}
