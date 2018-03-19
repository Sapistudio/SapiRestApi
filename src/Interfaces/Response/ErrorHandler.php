<?php
namespace SapiStudio\RestApi\Interfaces\Response;

/**
 * Interface ErrorHandler.
 */
interface ErrorHandler
{
    public function handle(array $data);
}
