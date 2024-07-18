<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Model;

use _JchOptimizeVendor\GuzzleHttp\RequestOptions;
use _JchOptimizeVendor\Spatie\Crawler\Crawler;
use _JchOptimizeVendor\Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;
use _JchOptimizeVendor\Spatie\Crawler\CrawlQueues\CrawlQueue;
use JchOptimize\Core\SystemUri;
use JchOptimize\Crawlers\ReCacheWithRedirect as ReCacheCrawler;
use Joomla\Registry\Registry;

\defined('_JEXEC') or exit('Restricted Access');
class ReCache
{
    private Registry $params;
    private CrawlQueue $crawlQueue;

    public function __construct(Registry $params, CrawlQueue $crawlQueue)
    {
        $this->params = $params;
        $this->crawlQueue = $crawlQueue;
    }

    public function reCache(string $redirectUrl = '')
    {
        $clientOptions = [RequestOptions::COOKIES => \false, RequestOptions::CONNECT_TIMEOUT => 10, RequestOptions::TIMEOUT => 10, RequestOptions::ALLOW_REDIRECTS => \true, RequestOptions::HEADERS => ['User-Agent' => '*']];
        $baseUrl = SystemUri::currentBaseFull();
        $crawlLimit = (int) $this->params->get('recache_crawl_limit', 500);
        $concurrency = (int) $this->params->get('recache_concurrency', 20);
        $maxDepth = (int) $this->params->get('recache_max_depth', 5);
        Crawler::create($clientOptions)->setCrawlQueue($this->crawlQueue)->setCrawlObserver(new ReCacheCrawler($redirectUrl))->ignoreRobots()->setTotalCrawlLimit($crawlLimit)->setConcurrency($concurrency)->setMaximumDepth($maxDepth)->setCrawlProfile(new CrawlInternalUrls($baseUrl))->startCrawling($baseUrl);
    }
}
