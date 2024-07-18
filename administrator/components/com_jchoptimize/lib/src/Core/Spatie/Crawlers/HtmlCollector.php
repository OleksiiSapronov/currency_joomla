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

namespace JchOptimize\Core\Spatie\Crawlers;

use _JchOptimizeVendor\GuzzleHttp\Exception\RequestException;
use _JchOptimizeVendor\GuzzleHttp\Psr7\Uri;
use _JchOptimizeVendor\Psr\Http\Message\ResponseInterface;
use _JchOptimizeVendor\Psr\Http\Message\UriInterface;
use _JchOptimizeVendor\Spatie\Crawler\CrawlObservers\CrawlObserver;
use JchOptimize\Core\Admin\Json;
use JchOptimize\Core\Helper;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class HtmlCollector extends CrawlObserver implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var list<array{url:string, html:string}>
     */
    private array $htmls = [];

    /**
     * @var list<Json>
     */
    private array $messages = [];
    private int $numUrls = 0;

    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null)
    {
        $body = $response->getBody();
        $body->rewind();
        $html = $body->getContents();
        if (Helper::validateHtml($html)) {
            $this->htmls[] = ['url' => (string) $url, 'html' => $html];
        }
        $originalUrl = Uri::withoutQueryValue($url, 'jchnooptimize');
        $message = 'Crawled URL: '.$originalUrl;
        $this->logger->info($message);
        $this->messages[] = new Json(null, $message);
        ++$this->numUrls;
    }

    public function crawlFailed(UriInterface $url, RequestException $requestException, ?UriInterface $foundOnUrl = null)
    {
        $message = 'Failed crawling url: '.Uri::withoutQueryValue($url, 'jchnooptimize').' with message '.$requestException->getMessage();
        $this->logger->error($message);
        $this->messages[] = new Json(new \Exception($message));
    }

    public function finishedCrawling()
    {
        $this->messages[] = new Json(null, 'Finished crawling '.$this->numUrls.' URLs');
    }

    /**
     * @return list<array{url:string, html:string}>
     */
    public function getHtmls(): array
    {
        return $this->htmls;
    }

    /**
     * @return list<Json>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
