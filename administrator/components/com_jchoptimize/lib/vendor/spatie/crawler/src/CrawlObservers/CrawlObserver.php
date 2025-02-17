<?php

namespace _JchOptimizeVendor\Spatie\Crawler\CrawlObservers;

use _JchOptimizeVendor\GuzzleHttp\Exception\RequestException;
use _JchOptimizeVendor\Psr\Http\Message\ResponseInterface;
use _JchOptimizeVendor\Psr\Http\Message\UriInterface;

abstract class CrawlObserver
{
    /**
     * Called when the crawler will crawl the url.
     *
     * @param \Psr\Http\Message\UriInterface $url
     */
    public function willCrawl(UriInterface $url)
    {
    }

    /**
     * Called when the crawler has crawled the given url successfully.
     *
     * @param \Psr\Http\Message\UriInterface      $url
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param null|\Psr\Http\Message\UriInterface $foundOnUrl
     */
    abstract public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null);

    /**
     * Called when the crawler had a problem crawling the given url.
     *
     * @param \Psr\Http\Message\UriInterface         $url
     * @param \GuzzleHttp\Exception\RequestException $requestException
     * @param null|\Psr\Http\Message\UriInterface    $foundOnUrl
     */
    abstract public function crawlFailed(UriInterface $url, RequestException $requestException, ?UriInterface $foundOnUrl = null);

    /**
     * Called when the crawl has ended.
     */
    public function finishedCrawling()
    {
    }
}
