<?php
namespace SapiStudio\RestApi\Response\Serialisers;

use SapiStudio\RestApi\Interfaces\Request\Serialiser;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerialiser;

/** Class JsonSerialiser.*/
class JsonSerialiser implements Serialiser
{
    /** JsonSerialiser::serialise() */
    public function serialise($input)
    {
        return (new SymfonySerialiser([new ObjectNormalizer()], [new JsonEncoder()]))->serialize($input, 'json');
    }
}
