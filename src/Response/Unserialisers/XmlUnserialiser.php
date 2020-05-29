<?php
namespace SapiStudio\RestApi\Response\Unserialisers;
use SapiStudio\RestApi\Interfaces\Response\Unserialiser;

/** Class XmlUnserialiser.*/
class XmlUnserialiser implements Unserialiser
{
    public function unserialise($input)
    {
        $decode = simplexml_load_string($input, null, LIBXML_NOCDATA);
        if (!$decode)
            throw new \Exception('Invalid xml format');
        return (array)json_decode(json_encode($decode),true);
    }
}
