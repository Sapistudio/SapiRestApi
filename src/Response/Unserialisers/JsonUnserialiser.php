<?php


namespace SapiStudio\RestApi\Response\Unserialisers;

use SapiStudio\RestApi\Interfaces\Response\Unserialiser;

/**
 * Class JsonUnserialiser.
 */
class JsonUnserialiser implements Unserialiser
{
    public function unserialise($input,$class = null)
    {
        return (array) json_decode($input);
    }
}