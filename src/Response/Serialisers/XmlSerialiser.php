<?php
namespace SapiStudio\RestApi\Response\Serialisers;

use SapiStudio\RestApi\Interfaces\Request\Serialiser;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerialiser;

/** Class XmlSerialiser.*/
class XmlSerialiser implements Serialiser
{
    /** XmlSerialiser::serialise() */
    public function serialise($input)
    {
        return (new SymfonySerialiser([new ObjectNormalizer()], [new XmlEncoder()]))->serialize($input, 'xml');
    }
}
