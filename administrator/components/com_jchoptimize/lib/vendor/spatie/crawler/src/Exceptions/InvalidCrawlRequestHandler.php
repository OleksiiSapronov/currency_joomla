<?php

namespace _JchOptimizeVendor\Spatie\Crawler\Exceptions;

class InvalidCrawlRequestHandler extends \RuntimeException
{
    public static function doesNotExtendBaseClass(string $handlerClass, string $baseClass)
    {
        return new static("`{$handlerClass} is not a valid handler class. A valid handler class should extend `{$baseClass}`.");
    }
}
