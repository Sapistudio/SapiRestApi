<?php
namespace SapiStudio\RestApi\Request;

use SapiStudio\RestApi\Interfaces\Request\ErrorHandler as RequestErrorHandler;
use SapiStudio\RestApi\Exceptions\RequestFailedException;
use GuzzleHttp\Exception\ClientException;

/** Class ErrorHandler.*/
class ErrorHandler implements RequestErrorHandler
{
    /** ErrorHandler::handle() */
    public function handle(ClientException $exceptionHandler)
    {
        throw new RequestFailedException($exceptionHandler->getMessage(),$exceptionHandler->getCode(),null,$exceptionHandler->getResponse());
    }
}
