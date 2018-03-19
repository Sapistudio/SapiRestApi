<?php
namespace SapiStudio\RestApi;

use SapiStudio\RestApi\Interfaces\HttpClient as HttpInterface;

/**
 * Class AbstractApi.
 */
abstract class AbstractApi
{
    protected $client;

    /**
     * AbstractApi constructor.
     *
     * @param HttpClient $client
     */
    public function __construct(HttpInterface $client)
    {
        $this->client = $client;
    }
    
    /**
     * AbstractApi::__call()
     * 
     * @return
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->client, $name], $arguments);
    }
}
