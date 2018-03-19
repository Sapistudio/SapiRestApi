<?php

namespace SapiStudio\RestApi\Exceptions;

use Exception;

class RequestFailedException extends Exception
{
    private $response;

    /**
     * RequestFailedException::__construct()
     * 
     * @return void
     */
    public function __construct($message, $code = 0, Exception $previous = null, $response = [])
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }
}