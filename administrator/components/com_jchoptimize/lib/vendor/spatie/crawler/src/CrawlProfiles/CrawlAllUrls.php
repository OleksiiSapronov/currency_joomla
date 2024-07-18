<?php

namespace _JchOptimizeVendor\Spatie\Crawler\CrawlProfiles;

use _JchOptimizeVendor\Psr\Http\Message\UriInterface;

class CrawlAllUrls extends CrawlProfile
{
    public function shouldCrawl(UriInterface $url): bool
    {
        return \true;
    }
}
