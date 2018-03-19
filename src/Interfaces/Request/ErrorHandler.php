<?php

namespace SapiStudio\RestApi\Interfaces\Request;

use GuzzleHttp\Exception\ClientException;

/**
 * Interface ErrorHandler.
 */
interface ErrorHandler
{
    /**
     * @param ClientException $e
     *
     * @throws RequestFailedException
     */
    public function handle(ClientException $e);
}
