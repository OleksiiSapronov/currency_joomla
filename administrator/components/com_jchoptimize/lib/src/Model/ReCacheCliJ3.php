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

namespace JchOptimize\Model;

use _JchOptimizeVendor\GuzzleHttp\RequestOptions;
use _JchOptimizeVendor\Spatie\Crawler\Crawler;
use _JchOptimizeVendor\Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;
use _JchOptimizeVendor\Spatie\Crawler\CrawlQueues\CrawlQueue;
use JchOptimize\Crawlers\ReCacheCliJ3 as ReCacheCliJ3Crawler;
use Joomla\CMS\Application\CliApplication;
use Joomla\Registry\Registry;

/**
 * @psalm-suppress all
 */
class ReCacheCliJ3
{
    private ReCacheCliJ3Crawler $observer;
    private Registry $params;
    private CrawlQueue $crawlQueue;

    public function __construct(Registry $params, CrawlQueue $crawlQueue)
    {
        $this->params = $params;
        $this->crawlQueue = $crawlQueue;
    }

    public function reCache(CliApplication $app, string $baseUrl): void
    {
        $clientOptions = [RequestOptions::COOKIES => \false, RequestOptions::CONNECT_TIMEOUT => 10, RequestOptions::TIMEOUT => 10, RequestOptions::ALLOW_REDIRECTS => \true, RequestOptions::HEADERS => ['User-Agent' => '*']];
        $crawlLimit = (int) ($app->input->getOption('crawl-limit') ?? $this->params->get('recache_crawl_limit', 500));
        $concurrency = (int) ($app->input->getOption('concurrency') ?? $this->params->get('recache_concurrency', 20));
        $maxDepth = (int) ($app->input->getOption('max-depth') ?? $this->params->get('recache_max_depth', 5));
        $this->observer = new ReCacheCliJ3Crawler($app);
        Crawler::create($clientOptions)->setCrawlQueue($this->crawlQueue)->setCrawlObserver($this->observer)->ignoreRobots()->setTotalCrawlLimit($crawlLimit)->setConcurrency($concurrency)->setParseableMimeTypes(['text/html'])->setMaximumDepth($maxDepth)->setCrawlProfile(new CrawlInternalUrls($baseUrl))->startCrawling($baseUrl);
    }

    public function getObserver(): ReCacheCliJ3Crawler
    {
        return $this->observer;
    }
}
