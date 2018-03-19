<?php

namespace SapiStudio\RestApi\Exceptions;

use Exception;

/**
 * Class InvalidResponseException.
 */
class InvalidResponseException extends Exception
{
    /**
     * @var array
     */
    private $response;

    /**
     * InvalidResponseException constructor.
     *
     */
    public function __construct($message, $code = 0, Exception $previous = null, $response = [])
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }
}
