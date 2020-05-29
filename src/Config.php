<?php
namespace SapiStudio\RestApi;

use Illuminate\Support\Collection;

/** Class Config.*/
class Config
{
    private $attributes;

    /** Config::__construct()*/
    public function __construct(array $attributes)
    {
        $this->attributes = new Collection($attributes);
    }

    /** Config::__get() */
    public function __get($key)
    {
        if ($this->attributes->has($key))
            return $this->attributes->get($key);
    }
}
