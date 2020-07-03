<?php

namespace AMBERSIVE\Api\Exceptions;

use Exception;

use \Illuminate\Http\Response as IlluminateResponse;

class EndpointValidationInvalid extends Exception
{

    public function __construct($validator)
    {
        parent::__construct("Request for this endpoint is invalid.");
        $this->validator = $validator;
    }


    /**
     * Get all of the validation error messages.
     *
     * @return array
     */
    public function errors()
    {
        return $this->validator->errors()->messages();
    }

    public function render($request) {
       
        return response([
            'status'  => IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY,
            'message' => $this->getMessage(),
            'errors'  => $this->errors()
        ], IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY);

    }

}
