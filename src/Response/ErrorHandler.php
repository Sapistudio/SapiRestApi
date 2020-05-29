<?php
namespace SapiStudio\RestApi\Response;

use SapiStudio\RestApi\Interfaces\Response\ErrorHandler as ResponseErrorHandler;
use SapiStudio\RestApi\Exceptions\InvalidResponseException;

/** Class ErrorHandler.*/
class ErrorHandler implements ResponseErrorHandler
{
    public function handle(array $data)
    {
        if (empty($data))
            throw new InvalidResponseException('Empty response received',400,null,$data);
    }
}
