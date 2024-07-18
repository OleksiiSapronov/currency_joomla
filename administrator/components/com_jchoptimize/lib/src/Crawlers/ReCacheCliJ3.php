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

namespace JchOptimize\Crawlers;

use _JchOptimizeVendor\GuzzleHttp\Exception\RequestException;
use _JchOptimizeVendor\Psr\Http\Message\ResponseInterface;
use _JchOptimizeVendor\Psr\Http\Message\UriInterface;
use _JchOptimizeVendor\Spatie\Crawler\CrawlObservers\CrawlObserver;
use Joomla\CMS\Application\CliApplication;

\defined('_JEXEC') or exit('Restricted Access');

/**
 * @psalm-suppress all
 */
class ReCacheCliJ3 extends CrawlObserver
{
    private CliApplication $cliApp;
    private int $numCrawled = 0;

    public function __construct(CliApplication $cliApp)
    {
        $this->cliApp = $cliApp;
    }

    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null)
    {
        $this->cliApp->out('Url crawled: '.$url);
        ++$this->numCrawled;
    }

    public function crawlFailed(UriInterface $url, RequestException $requestException, ?UriInterface $foundOnUrl = null)
    {
        $this->cliApp->out('Failed crawling url: '.$url);
    }

    public function getNumCrawled(): int
    {
        return $this->numCrawled;
    }
}
