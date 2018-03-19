<?php

namespace SapiStudio\RestApi;

use SapiStudio\RestApi\Interfaces\HttpClient as HttpInterface;
use SapiStudio\RestApi\Request\ErrorHandler as RequestErrorHandler;
use SapiStudio\RestApi\Response\ErrorHandler as ResponseErrorHandler;
use SapiStudio\RestApi\Response\Normaliser;
use GuzzleHttp\Client as GuzzleClient;
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

    /**
     * AbstractHttpClient constructor.
     *
     */
    public function __construct()
    {
        $this->requestErrorHandler = new RequestErrorHandler();
        $this->responseErrorHandler = new ResponseErrorHandler();
        $this->responseNormaliser = new Normaliser();
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function get($path=null,$parameters=null)
    {
        if($parameters)
            foreach($parameters as $key=>$parameter)
                $this->addQuery($key,$parameter);
        return $this->request('GET',$path);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function head($path=null,$parameters=null)
    {
        return $this->request('HEAD', $path);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function delete($path=null,$parameters=null)
    {
        return $this->request('DELETE', $path);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function put($path=null,$parameters=null)
    {
        return $this->request('PUT', $path);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function patch($path=null,$parameters=null)
    {
        return $this->request('PATCH', $path);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function post($path=null,$parameters=null)
    {
        if($parameters)
            foreach($parameters as $key=>$parameter)
                $this->addFormParameter($key,$parameter);
        return $this->request('POST', $path);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function options($path=null,$parameters=null)
    {
        return $this->request('OPTIONS', $path);
    }

    /**
     * @return GuzzleClient
     */
    public function getHttpClient()
    {
        $this->options['defaults']['headers'] = $this->getHeaders();
        if (class_exists($handler = $this->getHandler())) {
            $handler = new $handler($this);
            $this->setHandler($handler->create());
        }
        return new GuzzleClient($this->options);
    }

    /**
     * @param $baseUri
     * @param $path
     *
     * @return string
     */
    protected function buildRequestUri($baseUri, $path)
    {
        return $baseUri.$path;
    }

    protected function getHandler()
    {
    }

    /**
     * @param $method
     * @param $path
     *
     * @return mixed
     */
    private function request($method, $path)
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

        $request = new Request(
            $method,
            $this->buildRequestUri($modifiedClient->options['base_uri'], $path),
            $modifiedClient->headers
        );

        try {
            $response = $client->send($request, $modifiedClient->body);
        } catch (ClientException $e) {
            return $this->requestErrorHandler->handle($e);
        }

        return $modifiedClient->handleResponse($response->getBody());
    }

    /**
     * @param $response
     *
     * @return mixed
     */
    private function handleResponse($response)
    {
        $response = $this->responseNormaliser->normalise(
            $response, $this->responseFormat
        );
        if(!is_string($response))
            $this->responseErrorHandler->handle($response);
        return $response;
    }

    /**
     * @param $arguments
     *
     * @return AbstractHttpClient
     */
    private function applyModifiers($arguments)
    {
        $modifiers = $this->getRequestModifier();

        $modifiedClient = $this;

        foreach ($modifiers as $modifier) {
            $modifier = new $modifier($modifiedClient, $arguments);

            $modifiedClient = $modifier->apply();
        }

        return $modifiedClient;
    }

    /**
     * @return mixed
     */
    public function getQuery()
    {
        return array_get($this->body, 'query');
    }

    /**
     * @param $data
     */
    public function setQuery($data)
    {
        $this->body['query'] = array_merge(
            array_get($this->body, 'query', []), $data
        );
    }

    /**
     * @param $key
     * @param $value
     */
    public function addQuery($key, $value)
    {
        $this->body['query'][$key] = $value;
    }

    public function flushQuery()
    {
        unset($this->body['query']);
    }

    /**
     * @return mixed
     */
    public function getFormParameters()
    {
        return array_get($this->body, 'form_params');
    }

    /**
     * @param $data
     */
    public function setFormParameters($data)
    {
        $this->body['form_params'] = array_merge(
            array_get($this->body, 'form_params', []), $data
        );
    }

    /**
     * @param $key
     * @param $value
     */
    public function addFormParameter($key, $value)
    {
        $this->body['form_params'][$key] = $value;
    }

    public function flushFormParameters()
    {
        unset($this->body['form_params']);
    }

    /**
     * @return mixed
     */
    public function getJson()
    {
        return array_get($this->body, 'json');
    }

    /**
     * @param $data
     */
    public function setJson($data)
    {
        $this->body['json'] = array_merge(
            array_get($this->body, 'json', []), $data
        );
    }

    /**
     * @param $key
     * @param $value
     */
    public function addJson($key, $value)
    {
        $this->body['json'][$key] = $value;
    }

    public function flushJson()
    {
        unset($this->body['json']);
    }

    /**
     * @return mixed
     */
    public function getMultipart()
    {
        return array_get($this->body, 'multipart');
    }

    /**
     * @param $data
     */
    public function setMultipart($data)
    {
        $this->body['multipart'] = array_merge(
            array_get($this->body, 'multipart', []), $data
        );
    }

    /**
     * @param $name
     * @param $contents
     */
    public function addMultipart($name, $contents)
    {
        $this->body['multipart'][] = compact('name', 'contents');
    }

    public function flushMultipart()
    {
        unset($this->body['multipart']);
    }

    /**
     * @return mixed
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = array_merge($this->headers, $headers);
    }

    /**
     * @param $key
     * @param $value
     */
    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
    }

    public function flushHeaders()
    {
        unset($this->headers);
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getOption($key)
    {
        return $this->options[$key];
    }

    /**
     * @param $key
     * @param $value
     */
    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }
    
    /**
     * @param $key
     * @param $value
     */
    public function unsetOption($key)
    {
        unset($this->options[$key]);
    }

    /**
     * @param $path
     */
    public function setBaseUrl($path)
    {
        $this->options['base_url'] = $path;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setDefault($key, $value)
    {
        $this->options['defaults'][$key] = $value;
    }

    /**
     * @param $handler
     */
    public function setHandler($handler)
    {
        $this->options['handler'] = $handler;
    }

    /**
     * @param $modifier
     */
    public function addRequestModifier($modifier)
    {
        $this->requestModifiers[] = $modifier;
    }

    /**
     * @return array
     */
    public function getRequestModifier()
    {
        return $this->requestModifiers;
    }

    /**
     * @param $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getConfig($key)
    {
        if (!empty($this->config) && !empty($key)) {
            return $this->config->$key;
        }

        return $this->config;
    }

    /**
     * @param $class
     */
    public function setApiClass($class)
    {
        $this->apiClass = $class;
    }

    /**
     * @return mixed
     */
    public function getApiClass()
    {
        return $this->apiClass;
    }
}
