provides a unified interface for building API Clients

first create your client class
```php
use SapiStudio\RestApi\AbstractHttpClient;

/**
 * Class HttpClient.
 */
class HttpClient extends AbstractHttpClient
{
    protected $headers = [
          any custom header as array
    ];

    protected $requestModifiers = [RequestModifier::class];

    protected $responseFormat = 'xml json or txt';
    
    protected function buildRequestUri($baseUri,$path=false)
    {
        format your own custom request url
    }
}
```
Next create your modifier
```php
use SapiStudio\RestApi\Request\Modifier;

class RequestModifier extends Modifier
{
    public function apply()
    {
        $this->httpClient->setOption('base_uri', $this->httpClient->getConfig('your config key set on init'));//this is a required parameter,the base uri of the api call
        $this->httpClient->addFormParameter('apikey', $this->httpClient->getConfig('apikey'));//if you set an api key in config,you can use it here
        return $this->httpClient;
    }
}
```
And finally , your api class
```php
use SapiStudio\RestApi\AbstractApi;

class MyApi extends AbstractApi
{
    public function myapiFunction()
    {
        $this->addFormParameter('apifunc',$functionName);
        return $this->post(true,$parameters);
        return $this->get(true,$parameters);
    }
}
```
And now to use it
```php
$class      = new HttpClient();
$class->setConfig(['key'        => value]);
$apicall = $class->api('MyApi')->myapiFunction();
```
