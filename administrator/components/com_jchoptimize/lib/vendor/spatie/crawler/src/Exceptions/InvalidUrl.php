<?php

namespace _JchOptimizeVendor\Spatie\Crawler\Exceptions;

use _JchOptimizeVendor\Psr\Http\Message\UriInterface;
use _JchOptimizeVendor\Spatie\Crawler\CrawlUrl;

class InvalidUrl extends \Exception
{
    public static function unexpectedType($url)
    {
        $crawlUrlClass = CrawlUrl::class;
        $uriInterfaceClass = UriInterface::class;
        $givenUrlClass = \is_object($url) ? \get_class($url) : \gettype($url);

        return new static("You passed an invalid url of type `{$givenUrlClass}`. This should be either a {$crawlUrlClass} or `{$uriInterfaceClass}`");
    }
}
