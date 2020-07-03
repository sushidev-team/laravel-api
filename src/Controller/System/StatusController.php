<?php

namespace AMBERSIVE\Api\Controller\System;

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

class StatusController extends BaseApiController
{
    

    /**
     * @OA\Get(
     *     path="/api/status",
     *     operationId="SystemStatus",
     *     tags={"System"},
     *     @OA\Response(response="default", description="")
     * )
     */

    public function status(Request $request) {
        return $this->respondSuccess([]);        
    }

}