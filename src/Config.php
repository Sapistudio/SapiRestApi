<?php

/*
 * This file is part of Core.
 *
 * (c) DraperStudio <hello@draperstudio.tech>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SapiStudio\RestApi;

use Illuminate\Support\Collection;

/**
 * Class Config.
 */
class Config
{
    /**
     * @var Collection
     */
    private $attributes;

    /**
     * Config constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = new Collection($attributes);
    }

    /**
     * Get the private attributes.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        if ($this->attributes->has($key)) {
            return $this->attributes->get($key);
        }
    }
}
