<?php
namespace SapiStudio\RestApi;

use ReflectionClass;

/**
 * AbstractClient
 * 
 * @package 
 * @copyright 2018
 * @version $Id$
 * @access public
 */
abstract class AbstractClient
{
    private $container = [];
    private $httpClient;

    /**
     * AbstractClient::__construct()
     * 
     * @return
     */
    public function __construct()
    {
        $reflector = (new ReflectionClass(get_called_class()))->getNamespaceName().'\\HttpClient';
        $this->httpClient = new $reflector();        
    }

    /**
     * AbstractClient::api()
     * 
     * @return
     */
    public function api($name)
    {
        return $this->setInContainer((new ReflectionClass(get_called_class()))->getNamespaceName().'\\Api\\'.$name,$this->httpClient);
    }
    
    /**
     * AbstractClient::setInContainer()
     * 
     * @return
     */
    public function setInContainer($objectName = null,$argument=null){
        if(is_null($objectName))
            return false;
        if(!isset($this->container[md5($objectName)]))
            $this->container[md5($objectName)] = new $objectName($argument);
        return $this->container[md5($objectName)];
    }

    /**
     * AbstractClient::__call()
     * 
     * @return
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->httpClient, $name], $arguments);
    }
}