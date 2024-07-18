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
use Joomla\CMS\Language\Text;
use Symfony\Component\Console\Style\SymfonyStyle;

\defined('_JEXEC') or exit('Restricted Access');
class ReCacheCli extends CrawlObserver
{
    private SymfonyStyle $symfonyStyle;
    private int $numCrawled = 0;

    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }

    public function crawled(UriInterface $url, ResponseInterface $response, ?UriInterface $foundOnUrl = null)
    {
        $this->symfonyStyle->writeln(Text::sprintf('COM_JCHOPTIMIZE_CLI_URL_CRAWLED', $url));
        ++$this->numCrawled;
    }

    public function crawlFailed(UriInterface $url, RequestException $requestException, ?UriInterface $foundOnUrl = null)
    {
        $this->symfonyStyle->comment(Text::sprintf('COM_JCHOPTIMIZE_CLI_URL_CRAWL_FAILED', $url));
    }

    public function getNumCrawled(): int
    {
        return $this->numCrawled;
    }
}
