<?php

namespace AMBERSIVE\Api\Controller\System;

use Illuminate\Http\Request;

use AMBERSIVE\Api\Classes\EndpointRequest;
use AMBERSIVE\Api\Controller\BaseWebController;

use \Illuminate\Http\JsonResponse;

use Auth;
use DB;
use Hash;
use Validator;

use App\Models\Users\User;

use Carbon\Carbon;

class LicenseController extends BaseWebController
{
        
    /**
     * Return the license file for the current application
     *
     * @param  mixed $request
     * @return void
     */
    public function license(Request $request) {
        return $this->respondFileInBrowser($request, base_path('LICENSE.md'), []);
    }

}