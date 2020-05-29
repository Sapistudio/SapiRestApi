<?php
namespace SapiStudio\RestApi;

use SapiStudio\RestApi\Interfaces\HttpClient as HttpInterface;

/** Class AbstractApi.*/
abstract class AbstractApi
{
    protected $client;

    /** AbstractApi constructor. */
    public function __construct(HttpInterface $client)
    {
        $this->client = $client;
    }
    
    /** AbstractApi::__call()*/
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->client, $name], $arguments);
    }
}
