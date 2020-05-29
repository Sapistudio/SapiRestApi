<?php
namespace SapiStudio\RestApi\Response\Unserialisers;

use SapiStudio\RestApi\Interfaces\Response\Unserialiser;

/** Class PlainUnserialiser.*/
class PlainUnserialiser implements Unserialiser
{
    public function unserialise($input)
    {
        return (string)$input;
    }
}
