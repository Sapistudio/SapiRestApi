<?php
namespace SapiStudio\RestApi;

use ReflectionClass;
use SapiStudio\RestApi\Interfaces\HttpClient as HttpInterface;
use SapiStudio\RestApi\Request\ErrorHandler as RequestErrorHandler;
use SapiStudio\RestApi\Response\ErrorHandler as ResponseErrorHandler;
use SapiStudio\RestApi\Response\Normaliser;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;

/** Class AbstractHttpClient. wrapper against the guxzzle interface*/
abstract class AbstractHttpClient implements HttpInterface
{
    protected $body                 = [];
    protected $headers              = [];
    protected $options              = [];
    protected $responseFormat       = 'json';
    protected $requestModifiers     = [];
    private $apiContainer           = [];
    protected $requestErrorHandler;
    protected $responseErrorHandler;
    protected $responseNormaliser;
    protected $config;
    protected $responseStatusCode;
    protected $responseBodyContent;
    
    /** AbstractHttpClient::api() */
    public function api($name)
    {
        return $this->setInContainer((new ReflectionClass(get_called_class()))->getNamespaceName().'\\Api\\'.$name);
    }
    
    /** AbstractHttpClient::setInContainer() */
    protected function setInContainer($objectName = null){
        if(is_null($objectName))
            return false;
        if(!isset($this->apiContainer[md5($objectName)]))
            $this->apiContainer[md5($objectName)] = new $objectName($this);
        return $this->apiContainer[md5($objectName)];
    }
    
    /** AbstractHttpClient::__construct() */
    public function __construct()
    {
        $this->requestErrorHandler  = new RequestErrorHandler();
        $this->responseErrorHandler = new ResponseErrorHandler();
        $this->responseNormaliser   = new Normaliser();
    }

    /** AbstractHttpClient::get()*/
    public function get($path=null,$parameters=null)
    {
        if($parameters){
            foreach($parameters as $key=>$parameter)
                $this->addQuery($key,$parameter);
        }
        return $this->startRequest('GET',$path);
    }

    /** AbstractHttpClient::head() */
    public function head($path=null,$parameters=null)
    {
        return $this->startRequest('HEAD', $path);
    }

    /** AbstractHttpClient::delete()*/
    public function delete($path=null,$parameters=null)
    {
        return $this->startRequest('DELETE', $path);
    }

    /** AbstractHttpClient::put() */
    public function put($path=null,$parameters=null)
    {
        return $this->startRequest('PUT', $path);
    }

    /** AbstractHttpClient::patch()*/
    public function patch($path=null,$parameters=null)
    {
        return $this->startRequest('PATCH', $path);
    }

    /** AbstractHttpClient::post()  */
    public function post($path=null,$parameters=null)
    {
        if($parameters){
            foreach($parameters as $key=>$parameter)
                $this->addFormParameter($key,$parameter);
        }
        return $this->startRequest('POST', $path);
    }
    
    /** AbstractHttpClient::postJson()  */
    public function postJson($path=null,$parameters=null)
    {
        $this->setJson($parameters);
        return $this->startRequest('POST', $path);
    }
    
    /** AbstractHttpClient::cachedPostRequest() */
    public function cachedPostRequest($cacheHash = null,$path = null,$parameters = null)
    {
        return $this->startRequest('POST',$path,$parameters,$cacheHash);
    }
    
    /** AbstractHttpClient::cachedGetRequest() */
    public function cachedGetRequest($cacheHash = null,$path = null,$parameters = null)
    {
        if(!$path){
            $path       = $cacheHash;
            $cacheHash  = md5($cacheHash);
        }
        return $this->startRequest('GET',$path,$parameters,$cacheHash);
    }
    
    /** AbstractHttpClient::options()*/
    public function options($path=null,$parameters=null)
    {
        return $this->startRequest('OPTIONS', $path);
    }
    
    /** AbstractHttpClient::getRequestUri()*/
    public function getRequestUri($path=null,$parameters=null)
    {
        $url = $this->buildRequestUri($modifiedClient->options['base_uri'], $path);
        if($parameters)
            $url .= '?'.http_build_query(array_merge($parameters,$this->getQuery()));
        return $url;
    }

    /** AbstractHttpClient::getHttpClient() */
    public function getHttpClient()
    {
        $this->options['headers'] = $this->getHeaders();
        return \SapiStudio\Http\Browser\StreamClient::make($this->options);
    }
    
    /** AbstractHttpClient::buildRequestUri() */
    protected function buildRequestUri($baseUri, $path)
    {
        return $baseUri.$path;
    }

    /** AbstractHttpClient::startRequest()*/
    private function startRequest($method,$path,$customParamaters = [],$cacheName = false)
    {
        $modifiedClient = $this->getModifiedClient();
        $requestClient  = $modifiedClient->getHttpClient();
        $request        = new Request($method,$this->buildRequestUri($modifiedClient->options['base_uri'], $path),$modifiedClient->headers);
        try {
            if($cacheName && is_string($cacheName))
                $requestClient = $requestClient->cacheRequest($cacheName);
            $requestParams = (is_array($customParamaters) && !empty($customParamaters)) ? array_merge_recursive($modifiedClient->body,$customParamaters) : $modifiedClient->body;
            $response = $requestClient->send($request,$requestParams);
        } catch (ClientException $e) {
            return $this->requestErrorHandler->handle($e);
        }
        $this->responseStatusCode   = $response->getStatusCode();
        $this->responseBodyContent  = $response->getBody();
        $returnResponse             = $modifiedClient->handleResponse($response->getBody());
        return (method_exists($this,'validateApiResponse')) ? $this->validateApiResponse($returnResponse) : $returnResponse;
    }
    
    /** AbstractHttpClient::setFormat() */
    public function setFormat($responseFormat)
    {
        $this->responseFormat = $responseFormat;  
    }
    
    /** AbstractHttpClient::handleResponse() */
    private function handleResponse($response)
    {
        return $this->responseNormaliser->normalise($response, $this->responseFormat);
    }
    
    /** AbstractHttpClient::getModifiedClient() */
    private function getModifiedClient(){
        return $this->applyModifiers([
            'method'      => $method,
            'path'        => $path,
            'form_params' => $this->getFormParameters(),
            'multiplart'  => $this->getMultipart(),
            'query'       => $this->getQuery(),
            'json'        => $this->getJson(),
            'headers'     => $this->getHeaders(),
        ])->setHeaders($this->getHeaders());
    }
    
    /** AbstractHttpClient::applyModifiers()  */
    private function applyModifiers($arguments)
    {
        $modifiers      = $this->getRequestModifier();
        $modifiedClient = $this;
        if($modifiers){
            foreach ($modifiers as $modifier) {
                $modifiedClient = (new $modifier($modifiedClient, $arguments))->apply();
            }
        }
        return $modifiedClient;
    }

    /** AbstractHttpClient::getQuery() */
    public function getQuery()
    {
        return array_get($this->body, 'query');
    }

    /** AbstractHttpClient::setQuery()*/
    public function setQuery($data)
    {
        $this->body['query'] = array_merge(array_get($this->body, 'query', []), $data);
        return $this;
    }

    /** AbstractHttpClient::addQuery()*/
    public function addQuery($key, $value)
    {
        $this->body['query'][$key] = $value;
        return $this;
    }

    /** AbstractHttpClient::flushQuery()*/
    public function flushQuery()
    {
        unset($this->body['query']);
        return $this;
    }

    /** AbstractHttpClient::getFormParameters() */
    public function getFormParameters()
    {
        return array_get($this->body, 'form_params');
    }

    /** AbstractHttpClient::setFormParameters()*/
    public function setFormParameters($data)
    {
        $this->body['form_params'] = array_merge(array_get($this->body, 'form_params', []), $data);
        return $this;
    }

    /** AbstractHttpClient::addFormParameter() */
    public function addFormParameter($key, $value)
    {
        $this->body['form_params'][$key] = $value;
        return $this;
    }

    /** AbstractHttpClient::flushFormParameters() */
    public function flushFormParameters()
    {
        unset($this->body['form_params']);
        return $this;
    }

    /** AbstractHttpClient::getJson()*/
    public function getJson()
    {
        return array_get($this->body, 'json');
    }

    /** AbstractHttpClient::setJson() */
    public function setJson($data)
    {
        $this->body['json'] = array_merge(array_get($this->body, 'json', []), $data);
        return $this;
    }

    /** AbstractHttpClient::addJson()*/
    public function addJson($key, $value)
    {
        $this->body['json'][$key] = $value;
        return $this;
    }

    /** AbstractHttpClient::flushJson() */
    public function flushJson()
    {
        unset($this->body['json']);
        return $this;
    }

    /** AbstractHttpClient::getMultipart()  */
    public function getMultipart()
    {
        return array_get($this->body, 'multipart');
    }

    /** AbstractHttpClient::setMultipart() */
    public function setMultipart($data)
    {
        $this->body['multipart'] = array_merge(array_get($this->body, 'multipart', []), $data);
        return $this;
    }

    /** AbstractHttpClient::addMultipart() */
    public function addMultipart($name, $contents)
    {
        $this->body['multipart'][] = compact('name', 'contents');
        return $this;
    }

    /** AbstractHttpClient::flushMultipart()*/
    public function flushMultipart()
    {
        unset($this->body['multipart']);
        return $this;
    }

    /** AbstractHttpClient::getHeaders() */
    public function getHeaders()
    {
        return $this->headers;
    }

    /** AbstractHttpClient::setHeaders() */
    public function setHeaders($headers)
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /** AbstractHttpClient::addHeader()*/
    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /** AbstractHttpClient::flushHeaders() */
    public function flushHeaders()
    {
        unset($this->headers);
        return $this;
    }

    /** AbstractHttpClient::getOption()*/
    public function getOption($key)
    {
        return $this->options[$key];
    }

    /** AbstractHttpClient::setOption() */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }
    
    /** AbstractHttpClient::unsetOption()*/
    public function unsetOption($key)
    {
        unset($this->options[$key]);
    }

    /** AbstractHttpClient::setBaseUrl() */
    public function setBaseUrl($path)
    {
        $this->options['base_url'] = $path;
        return $this;
    }

    /** AbstractHttpClient::setDefault()*/
    public function setDefault($key, $value)
    {
        $this->options['defaults'][$key] = $value;
        return $this;
    }

    /** AbstractHttpClient::setHandler() */
    public function setHandler($handler)
    {
        $this->options['handler'] = $handler;
        return $this;
    }

    /** AbstractHttpClient::addRequestModifier()*/
    public function addRequestModifier($modifier)
    {
        $this->requestModifiers[] = $modifier;
        return $this;
    }

    /** AbstractHttpClient::getRequestModifier() */
    public function getRequestModifier()
    {
        return $this->requestModifiers;
    }

    /** AbstractHttpClient::setConfig()*/
    public function setConfig($config = [])
    {
        $this->config = new Config($config);
        return $this;
    }

    /** AbstractHttpClient::getConfig()*/
    public function getConfig($key)
    {
        return $this->config->$key;
    }
}
