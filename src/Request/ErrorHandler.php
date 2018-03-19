<?php
namespace SapiStudio\RestApi\Request;

use SapiStudio\RestApi\Interfaces\Request\ErrorHandler as RequestErrorHandler;
use SapiStudio\RestApi\Exceptions\RequestFailedException;
use GuzzleHttp\Exception\ClientException;

/**
 * Class ErrorHandler.
 */
class ErrorHandler implements RequestErrorHandler
{
    /**
     * @param ClientException $e
     *
     * @throws RequestFailedException
     */
    public function handle(ClientException $e)
    {
        throw new RequestFailedException(
            $e->getMessage(),
            $e->getCode(),
            null,
            $e->getResponse()
        );
    }
}
