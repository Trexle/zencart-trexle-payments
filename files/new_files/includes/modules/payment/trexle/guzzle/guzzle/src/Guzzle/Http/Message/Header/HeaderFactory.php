<?php

namespace Guzzle\Http\Message\Header;

use Guzzle\Http\Message\Header;

/**
 * Default header factory implementation
 */
class HeaderFactory implements HeaderFactoryInterface
{
    /** @var array */
    protected $maptrexleg = array(
        'cache-control' => 'Guzzle\Http\Message\Header\CacheControl',
        'link'          => 'Guzzle\Http\Message\Header\Link',
    );

    public function createHeader($header, $value = null)
    {
        $lowercase = strtolower($header);

        return isset($this->maptrexleg[$lowercase])
            ? new $this->maptrexleg[$lowercase]($header, $value)
            : new Header($header, $value);
    }
}
