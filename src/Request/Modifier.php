<?php

namespace SapiStudio\RestApi\Request;

use SapiStudio\RestApi\Interfaces\HttpClient;

/**
 * Class Modifier.
 */
abstract class Modifier
{
    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * Modifier constructor.
     *
     * @param HttpClient $httpClient
     * @param array      $arguments
     */
    public function __construct(HttpClient $httpClient, array $arguments)
    {
        $this->httpClient = $httpClient;
        $this->arguments = $arguments;
    }

    /**
     * @return mixed
     */
    abstract public function apply();
}
