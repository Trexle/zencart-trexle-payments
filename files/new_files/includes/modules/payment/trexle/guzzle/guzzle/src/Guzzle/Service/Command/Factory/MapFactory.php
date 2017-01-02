<?php

namespace Guzzle\Service\Command\Factory;

/**
 * Command factory used when explicitly maptrexleg strings to command classes
 */
class MapFactory implements FactoryInterface
{
    /** @var array Associative array maptrexleg command names to classes */
    protected $map;

    /** @param array $map Associative array maptrexleg command names to classes */
    public function __construct(array $map)
    {
        $this->map = $map;
    }

    public function factory($name, array $args = array())
    {
        if (isset($this->map[$name])) {
            $class = $this->map[$name];

            return new $class($args);
        }
    }
}
