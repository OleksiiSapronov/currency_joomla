<?php

namespace _JchOptimizeVendor\Spatie\Crawler\CrawlObservers;

use _JchOptimizeVendor\GuzzleHttp\Exception\RequestException;
use _JchOptimizeVendor\Psr\Http\Message\ResponseInterface;
use _JchOptimizeVendor\Spatie\Crawler\CrawlUrl;

class CrawlObserverCollection implements \ArrayAccess, \Iterator
{
    /** @var \Spatie\Crawler\CrawlObservers\CrawlObserver[] */
    protected array $observers;
    protected int $position;

    public function __construct(array $observers = [])
    {
        $this->observers = $observers;
        $this->position = 0;
    }

    public function addObserver(CrawlObserver $observer)
    {
        $this->observers[] = $observer;
    }

    public function crawled(CrawlUrl $crawlUrl, ResponseInterface $response)
    {
        foreach ($this->observers as $crawlObserver) {
            $crawlObserver->crawled($crawlUrl->url, $response, $crawlUrl->foundOnUrl);
        }
    }

    public function crawlFailed(CrawlUrl $crawlUrl, RequestException $exception)
    {
        foreach ($this->observers as $crawlObserver) {
            $crawlObserver->crawlFailed($crawlUrl->url, $exception, $crawlUrl->foundOnUrl);
        }
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->observers[$this->position];
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return isset($this->observers[$offset]) ? $this->observers[$offset] : null;
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value)
    {
        if (\is_null($offset)) {
            $this->observers[] = $value;
        } else {
            $this->observers[$offset] = $value;
        }
    }

    #[\ReturnTypeWillChange]
    public function offsetExists($offset)
    {
        return isset($this->observers[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset)
    {
        unset($this->observers[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        ++$this->position;
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->position;
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return isset($this->observers[$this->position]);
    }

    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->position = 0;
    }
}
