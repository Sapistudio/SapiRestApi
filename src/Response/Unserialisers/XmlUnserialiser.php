<?php

namespace SapiStudio\RestApi\Response\Unserialisers;

use SapiStudio\RestApi\Interfaces\Response\Unserialiser;

/**
 * Class XmlUnserialiser.
 */
class XmlUnserialiser implements Unserialiser
{
    public function unserialise($input,$class = null)
    {
        return (array) json_decode(json_encode(simplexml_load_string($input, null, LIBXML_NOCDATA)),true);
    }
}
