provides a unified interface for building API Clients
```php
use SapiStudio\RestApi\AbstractHttpClient;

/**
 * Class HttpClient.
 */
class HttpClient extends AbstractHttpClient
{
    /**
     * {@inheritdoc}
     */
    protected $headers = [
           'User-Agent' => 'Apivore/Hitpath',
    ];

    /**
     * {@inheritdoc}
     */
    protected $requestModifiers = [\Affiliates\Clients\Hitpath\Request\Modifiers\RequestModifier::class];

    /**
     * @var string
     */
    protected $responseFormat = 'xml';
    
    /**
     * @param $baseUri
     * @param $path
     *
     * @return string
     */
    protected function buildRequestUri($baseUri,$path=false)
    {
        $parsed = parse_url($baseUri);
        $return = (is_bool($path) && $path==true) ? $parsed['scheme'].'://reporting.'.$parsed['host'].'/api.php' : $parsed['scheme'].'://api.'.$parsed['host'].'/pubapi.php';
        return $return;
    }
}
HeadlessChrome::url('https://example.com')->save($pathToImage);
```
