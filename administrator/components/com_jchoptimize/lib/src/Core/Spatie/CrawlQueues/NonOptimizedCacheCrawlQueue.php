<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2023 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\Spatie\CrawlQueues;

use _JchOptimizeVendor\GuzzleHttp\Psr7\Uri;
use _JchOptimizeVendor\Psr\Http\Message\UriInterface;
use _JchOptimizeVendor\Spatie\Crawler\CrawlUrl;

class NonOptimizedCacheCrawlQueue extends \JchOptimize\Core\Spatie\CrawlQueues\CacheCrawlQueue
{
    protected function getUrlId($crawlUrl): string
    {
        if ($crawlUrl instanceof CrawlUrl) {
            $crawlUrl->url = $this->modifyUrl($crawlUrl->url);
        }
        if ($crawlUrl instanceof UriInterface) {
            $crawlUrl = $this->modifyUrl($crawlUrl);
        }

        return parent::getUrlId($crawlUrl);
    }

    private function modifyUrl(UriInterface $url): UriInterface
    {
        return Uri::withQueryValues($url, ['jchnooptimize' => '1']);
    }
}
