<?php
namespace SapiStudio\RestApi\Response;

use SapiStudio\RestApi\Interfaces\Response\Normaliser as NormaliserContract;
use InvalidArgumentException;

/** Class Normaliser.*/
class Normaliser implements NormaliserContract
{
    /** Normaliser::normalise() */
    public function normalise($response, $format)
    {
        switch ($format){
            case 'json':
                $response = $this->getJsonUnserialiser()->unserialise($response);
                break;
            case 'xml':
                $response = $this->getXmlUnserialiser()->unserialise($response);
                break;
            case 'plain':
            case 'stream':
                $response = $this->getPlainUnserialiser()->unserialise($response);
                break;
            case 'image':
                break;
            default:
                throw new InvalidArgumentException('Invalid response format specified.');
                break;
        }

        return $response;
    }

    /** Normaliser::getJsonUnserialiser() */
    protected function getJsonUnserialiser()
    {
        return new Unserialisers\JsonUnserialiser();
    }
    
    /** Normaliser::getXmlUnserialiser() */
    protected function getXmlUnserialiser()
    {
        return new Unserialisers\XmlUnserialiser();
    }

    /** Normaliser::getPlainUnserialiser() */
    protected function getPlainUnserialiser()
    {
        return new Unserialisers\PlainUnserialiser();
    }
}
