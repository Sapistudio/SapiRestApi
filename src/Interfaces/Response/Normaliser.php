<?php

namespace SapiStudio\RestApi\Interfaces\Response;

/**
 * Interface Normaliser.
 */
interface Normaliser
{
    public function normalise($response, $format);
}
