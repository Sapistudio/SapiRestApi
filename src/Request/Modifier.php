<?php
namespace SapiStudio\RestApi\Request;

use SapiStudio\RestApi\Interfaces\HttpClient;

/** Class Modifier. */
abstract class Modifier
{
    protected $httpClient;
    protected $arguments;

    /** Modifier::__construct()*/
    public function __construct(HttpClient $httpClient, array $arguments)
    {
        $this->httpClient = $httpClient;
        $this->arguments = $arguments;
    }

    /** Modifier::apply()*/
    abstract public function apply();
}
