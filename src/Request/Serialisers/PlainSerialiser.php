<?php

namespace SapiStudio\RestApi\Response\Serialisers;

use SapiStudio\RestApi\Interfaces\Request\Serialiser;

/**
 * Class PlainSerialiser.
 */
class PlainSerialiser implements Serialiser
{
    /**
     * @param $input
     *
     * @return mixed
     */
    public function serialise($input)
    {
        return $input;
    }
}
