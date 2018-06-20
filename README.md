provides a unified interface for building API Clients
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

    protected $requestModifiers = [your own modifier class];

    protected $responseFormat = 'xml json or txt';
    
    protected function buildRequestUri($baseUri,$path=false)
    {
        format your own custom request url
    }
}
```
