<?php

namespace _JchOptimizeVendor\Spatie\Crawler;

use _JchOptimizeVendor\GuzzleHttp\Psr7\Uri;
use _JchOptimizeVendor\Psr\Http\Message\UriInterface;
use _JchOptimizeVendor\Symfony\Component\DomCrawler\Crawler as DomCrawler;
use _JchOptimizeVendor\Symfony\Component\DomCrawler\Link;
use _JchOptimizeVendor\Tree\Node\Node;

use function _JchOptimizeVendor\collect;

class LinkAdder
{
    protected Crawler $crawler;

    public function __construct(Crawler $crawler)
    {
        $this->crawler = $crawler;
    }

    public function addFromHtml(string $html, UriInterface $foundOnUrl)
    {
        $allLinks = $this->extractLinksFromHtml($html, $foundOnUrl);
        collect($allLinks)->filter(function (UriInterface $url) {
            return $this->hasCrawlableScheme($url);
        })->map(function (UriInterface $url) {
            return $this->normalizeUrl($url);
        })->filter(function (UriInterface $url) use ($foundOnUrl) {
            if (!($node = $this->crawler->addToDepthTree($url, $foundOnUrl))) {
                return \false;
            }

            return $this->shouldCrawl($node);
        })->filter(function (UriInterface $url) {
            return \false === \strpos($url->getPath(), '/tel:');
        })->each(function (UriInterface $url) use ($foundOnUrl) {
            $crawlUrl = CrawlUrl::create($url, $foundOnUrl);
            $this->crawler->addToCrawlQueue($crawlUrl);
        });
    }

    /**
     * @param \Psr\Http\Message\UriInterface $foundOnUrl
     *
     * @return null|\Illuminate\Support\Collection
     */
    protected function extractLinksFromHtml(string $html, UriInterface $foundOnUrl)
    {
        $domCrawler = new DomCrawler($html, $foundOnUrl);

        return collect($domCrawler->filterXpath('//a | //link[@rel="next" or @rel="prev"]')->links())->reject(function (Link $link) {
            if ($this->isInvalidHrefNode($link)) {
                return \true;
            }
            if ($this->crawler->mustRejectNofollowLinks() && 'nofollow' === $link->getNode()->getAttribute('rel')) {
                return \true;
            }

            return \false;
        })->map(function (Link $link) {
            try {
                return new Uri($link->getUri());
            } catch (\InvalidArgumentException $exception) {
                return;
            }
        })->filter();
    }

    protected function hasCrawlableScheme(UriInterface $uri): bool
    {
        return \in_array($uri->getScheme(), ['http', 'https']);
    }

    protected function normalizeUrl(UriInterface $url): UriInterface
    {
        return $url->withFragment('');
    }

    protected function shouldCrawl(Node $node): bool
    {
        if ($this->crawler->mustRespectRobots() && !$this->crawler->getRobotsTxt()->allows($node->getValue(), $this->crawler->getUserAgent())) {
            return \false;
        }
        $maximumDepth = $this->crawler->getMaximumDepth();
        if (\is_null($maximumDepth)) {
            return \true;
        }

        return $node->getDepth() <= $maximumDepth;
    }

    protected function isInvalidHrefNode(Link $link): bool
    {
        if ('a' !== $link->getNode()->nodeName) {
            return \false;
        }
        if (null !== $link->getNode()->nextSibling) {
            return \false;
        }
        if (0 !== $link->getNode()->childNodes->length) {
            return \false;
        }

        return \true;
    }
}
