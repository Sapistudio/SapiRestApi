<?php
namespace SapiStudio\RestApi\Exceptions;

use Exception;

/** Class InvalidResponseException.*/
class InvalidResponseException extends Exception
{
    private $response;

    /** InvalidResponseException::__construct()*/
    public function __construct($message, $code = 0, Exception $previous = null, $response = [])
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    /** InvalidResponseException::getResponse() */
    public function getResponse()
    {
        return $this->response;
    }
}
