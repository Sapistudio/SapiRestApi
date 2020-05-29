<?php

namespace SapiStudio\RestApi\Response\Serialisers;

use SapiStudio\RestApi\Interfaces\Request\Serialiser;

/** Class PlainSerialiser.*/
class PlainSerialiser implements Serialiser
{
    /** PlainSerialiser::serialise()*/
    public function serialise($input)
    {
        return $input;
    }
}
