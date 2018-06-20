<?php
namespace SapiStudio\RestApi;

use ReflectionClass;
use SapiStudio\RestApi\Interfaces\HttpClient as HttpInterface;
use SapiStudio\RestApi\Request\ErrorHandler as RequestErrorHandler;
use SapiStudio\RestApi\Response\ErrorHandler as ResponseErrorHandler;
use SapiStudio\RestApi\Response\Normaliser;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;

/**
 * Class AbstractHttpClient.
 */
abstract class AbstractHttpClient implements HttpInterface
{
    /**
     * @var array
     */
    protected $body = [];

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var string
     */
    protected $responseFormat = 'json';

    /**
     * @var ErrorHandler
     */
    protected $requestErrorHandler;

    /**
     * @var ErrorHandler
     */
    protected $responseErrorHandler;

    /**
     * @var Normaliser
     */
    protected $responseNormaliser;

    /**
     * @var array
     */
    protected $requestModifiers = [];

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $apiClass;
    
    
    private $apiContainer = [];
    
   
    /**
     * AbstractHttpClient::api()
     * 
     * @return
     */
    public function api($name)
    {
        return $this->setInContainer((new ReflectionClass(get_called_class()))->getNamespaceName().'\\Api\\'.$name);
    }
    
    /**
     * AbstractHttpClient::setInContainer()
     * 
     * @return
     */
    protected function setInContainer($objectName = null){
        if(is_null($objectName))
            return false;
        if(!isset($this->apiContainer[md5($objectName)]))
            $this->apiContainer[md5($objectName)] = new $objectName($this);
        return $this->apiContainer[md5($objectName)];
    }
    
    /**
     * AbstractHttpClient::__construct()
     * 
     * @return
     */
    public function __construct()
    {
        $this->requestErrorHandler  = new RequestErrorHandler();
        $this->responseErrorHandler = new ResponseErrorHandler();
        $this->responseNormaliser   = new Normaliser();
    }

    /**
     * AbstractHttpClient::get()
     * 
     * @return
     */
    public function get($path=null,$parameters=null)
    {
        if($parameters){
            foreach($parameters as $key=>$parameter)
                $this->addQuery($key,$parameter);
        }
        return $this->startRequest('GET',$path);
    }

    /**
     * AbstractHttpClient::head()
     * 
     * @return
     */
    public function head($path=null,$parameters=null)
    {
        return $this->startRequest('HEAD', $path);
    }

    /**
     * AbstractHttpClient::delete()
     * 
     * @return
     */
    public function delete($path=null,$parameters=null)
    {
        return $this->startRequest('DELETE', $path);
    }

    /**
     * AbstractHttpClient::put()
     * 
     * @return
     */
    public function put($path=null,$parameters=null)
    {
        return $this->startRequest('PUT', $path);
    }

    /**
     * AbstractHttpClient::patch()
     * 
     * @return
     */
    public function patch($path=null,$parameters=null)
    {
        return $this->startRequest('PATCH', $path);
    }

    /**
     * AbstractHttpClient::post()
     * 
     * @return
     */
    public function post($path=null,$parameters=null)
    {
        if($parameters){
            foreach($parameters as $key=>$parameter)
                $this->addFormParameter($key,$parameter);
        }
        return $this->startRequest('POST', $path);
    }

    /**
     * AbstractHttpClient::options()
     * 
     * @return
     */
    public function options($path=null,$parameters=null)
    {
        return $this->startRequest('OPTIONS', $path);
    }

    /**
     * AbstractHttpClient::getHttpClient()
     * 
     * @return
     */
    public function getHttpClient()
    {
        $this->options['headers'] = $this->getHeaders();
        return \SapiStudio\Http\Browser\CurlClient::getDefaultClient($this->options);
    }

    /**
     * AbstractHttpClient::buildRequestUri()
     * 
     * @return
     */
    protected function buildRequestUri($baseUri, $path)
    {
        return $baseUri.$path;
    }

    /**
     * AbstractHttpClient::startRequest()
     * 
     * @return
     */
    private function startRequest($method, $path)
    {
        $modifiedClient = $this->applyModifiers([
            'method'      => $method,
            'path'        => $path,
            'form_params' => $this->getFormParameters(),
            'multiplart'  => $this->getMultipart(),
            'query'       => $this->getQuery(),
            'json'        => $this->getJson(),
            'headers'     => $this->getHeaders(),
        ]);

        $modifiedClient->setHeaders($this->getHeaders());
        $client = $modifiedClient->getHttpClient();
        $request = new Request($method,$this->buildRequestUri($modifiedClient->options['base_uri'], $path),$modifiedClient->headers);
        try {
            $response = $client->send($request, $modifiedClient->body);
        } catch (ClientException $e) {
            return $this->requestErrorHandler->handle($e);
        }
        return $modifiedClient->handleResponse($response->getBody());
    }

    /**
     * AbstractHttpClient::handleResponse()
     * 
     * @return
     */
    private function handleResponse($response)
    {
        $response = $this->responseNormaliser->normalise($response, $this->responseFormat);
        return (!is_string($response)) ? $this->responseErrorHandler->handle($response) : $response;
    }

    /**
     * AbstractHttpClient::applyModifiers()
     * 
     * @return
     */
    private function applyModifiers($arguments)
    {
        $modifiers      = $this->getRequestModifier();
        $modifiedClient = $this;
        if($modifiers){
            foreach ($modifiers as $modifier) {
                $modifier = new $modifier($modifiedClient, $arguments);
                $modifiedClient = $modifier->apply();
            }
        }
        return $modifiedClient;
    }

    /**
     * AbstractHttpClient::getQuery()
     * 
     * @return
     */
    public function getQuery()
    {
        return array_get($this->body, 'query');
    }

    /**
     * AbstractHttpClient::setQuery()
     * 
     * @return
     */
    public function setQuery($data)
    {
        $this->body['query'] = array_merge(array_get($this->body, 'query', []), $data);
    }

    /**
     * AbstractHttpClient::addQuery()
     * 
     * @return
     */
    public function addQuery($key, $value)
    {
        $this->body['query'][$key] = $value;
    }

    /**
     * AbstractHttpClient::flushQuery()
     * 
     * @return
     */
    public function flushQuery()
    {
        unset($this->body['query']);
    }

    /**
     * AbstractHttpClient::getFormParameters()
     * 
     * @return
     */
    public function getFormParameters()
    {
        return array_get($this->body, 'form_params');
    }

    /**
     * AbstractHttpClient::setFormParameters()
     * 
     * @return
     */
    public function setFormParameters($data)
    {
        $this->body['form_params'] = array_merge(array_get($this->body, 'form_params', []), $data);
    }

    /**
     * AbstractHttpClient::addFormParameter()
     * 
     * @return
     */
    public function addFormParameter($key, $value)
    {
        $this->body['form_params'][$key] = $value;
    }

    /**
     * AbstractHttpClient::flushFormParameters()
     * 
     * @return
     */
    public function flushFormParameters()
    {
        unset($this->body['form_params']);
    }

    /**
     * AbstractHttpClient::getJson()
     * 
     * @return
     */
    public function getJson()
    {
        return array_get($this->body, 'json');
    }

    /**
     * AbstractHttpClient::setJson()
     * 
     * @return
     */
    public function setJson($data)
    {
        $this->body['json'] = array_merge(array_get($this->body, 'json', []), $data);
    }

    /**
     * AbstractHttpClient::addJson()
     * 
     * @return
     */
    public function addJson($key, $value)
    {
        $this->body['json'][$key] = $value;
    }

    /**
     * AbstractHttpClient::flushJson()
     * 
     * @return
     */
    public function flushJson()
    {
        unset($this->body['json']);
    }

    /**
     * AbstractHttpClient::getMultipart()
     * 
     * @return
     */
    public function getMultipart()
    {
        return array_get($this->body, 'multipart');
    }

    /**
     * AbstractHttpClient::setMultipart()
     * 
     * @return
     */
    public function setMultipart($data)
    {
        $this->body['multipart'] = array_merge(array_get($this->body, 'multipart', []), $data);
    }

    /**
     * AbstractHttpClient::addMultipart()
     * 
     * @return
     */
    public function addMultipart($name, $contents)
    {
        $this->body['multipart'][] = compact('name', 'contents');
    }

    /**
     * AbstractHttpClient::flushMultipart()
     * 
     * @return
     */
    public function flushMultipart()
    {
        unset($this->body['multipart']);
    }

    /**
     * AbstractHttpClient::getHeaders()
     * 
     * @return
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * AbstractHttpClient::setHeaders()
     * 
     * @return
     */
    public function setHeaders($headers)
    {
        $this->headers = array_merge($this->headers, $headers);
    }

    /**
     * AbstractHttpClient::addHeader()
     * 
     * @return
     */
    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    /**
     * AbstractHttpClient::flushHeaders()
     * 
     * @return
     */
    public function flushHeaders()
    {
        unset($this->headers);
    }

    /**
     * AbstractHttpClient::getOption()
     * 
     * @return
     */
    public function getOption($key)
    {
        return $this->options[$key];
    }

    /**
     * AbstractHttpClient::setOption()
     * 
     * @return
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }
    
    /**
     * AbstractHttpClient::unsetOption()
     * 
     * @return
     */
    public function unsetOption($key)
    {
        unset($this->options[$key]);
    }

    /**
     * AbstractHttpClient::setBaseUrl()
     * 
     * @return
     */
    public function setBaseUrl($path)
    {
        $this->options['base_url'] = $path;
    }

    /**
     * AbstractHttpClient::setDefault()
     * 
     * @return
     */
    public function setDefault($key, $value)
    {
        $this->options['defaults'][$key] = $value;
    }

    /**
     * AbstractHttpClient::setHandler()
     * 
     * @return
     */
    public function setHandler($handler)
    {
        $this->options['handler'] = $handler;
    }

    /**
     * AbstractHttpClient::addRequestModifier()
     * 
     * @return
     */
    public function addRequestModifier($modifier)
    {
        $this->requestModifiers[] = $modifier;
    }

    /**
     * AbstractHttpClient::getRequestModifier()
     * 
     * @return
     */
    public function getRequestModifier()
    {
        return $this->requestModifiers;
    }

    /**
     * AbstractHttpClient::setConfig()
     * 
     * @return
     */
    public function setConfig($config = [])
    {
        $this->config = new Config($config);
    }

    /**
     * AbstractHttpClient::getConfig()
     * 
     * @return
     */
    public function getConfig($key)
    {
        return $this->config->$key;
    }
}
