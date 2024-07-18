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

namespace JchOptimize\Command;

use _JchOptimizeVendor\GuzzleHttp\RequestOptions;
use _JchOptimizeVendor\Spatie\Crawler\Crawler;
use _JchOptimizeVendor\Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;
use _JchOptimizeVendor\Spatie\Crawler\CrawlQueues\CrawlQueue;
use JchOptimize\ContainerFactory;
use JchOptimize\Crawlers\ReCacheCli;
use JchOptimize\Model\Cache;
use Joomla\CMS\Language\Text;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Registry\Registry;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ReCache extends AbstractCommand
{
    /**
     * Default command name.
     *
     * @var null|string
     */
    protected static $defaultName = 'jchoptimize:recache';

    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);
        $symfonyStyle->title(Text::_('COM_JCHOPTIMIZE_CLI_RECACHE_TITLE'));

        /** @var string $baseUrl */
        $baseUrl = $input->getOption('live-site') ?? $this->getApplication()->get('live_site', '');
        if (!$baseUrl) {
            $symfonyStyle->error(Text::_('COM_JCHOPTIMIZE_CLI_BASE_URL_NOT_SET'));

            return 255;
        }
        $container = ContainerFactory::getContainer();
        // First flush the cache
        if (!$input->getOption('no-delete-cache')) {
            /** @var Cache $cache */
            $cache = $container->get(Cache::class);
            $cache->cleanCache();
            $symfonyStyle->comment(Text::_('COM_JCHOPTIMIZE_CLI_CACHE_CLEANED'));
        }
        $symfonyStyle->section(Text::_('COM_JCHOPTIMIZE_CLI_RECACHE_START'));
        $clientOptions = [RequestOptions::COOKIES => \false, RequestOptions::CONNECT_TIMEOUT => 100, RequestOptions::TIMEOUT => 100, RequestOptions::ALLOW_REDIRECTS => \true, RequestOptions::HEADERS => ['User-Agent' => '*']];

        /** @var CrawlQueue $cacheCrawlQueue */
        $cacheCrawlQueue = $container->get(CrawlQueue::class);

        /** @var Registry $params */
        $params = $container->get('params');
        $crawlLimit = (int) ($input->getOption('crawl-limit') ?? $params->get('recache_crawl_limit', 500));
        $concurrency = (int) ($input->getOption('concurrency') ?? $params->get('recache_concurrency', 20));
        $maxDepth = (int) ($input->getOption('max-depth') ?? $params->get('recache_max_depth', 5));
        $observer = new ReCacheCli($symfonyStyle);
        Crawler::create($clientOptions)->setCrawlQueue($cacheCrawlQueue)->setCrawlObserver($observer)->setParseableMimeTypes(['text/html'])->ignoreRobots()->setTotalCrawlLimit($crawlLimit)->setConcurrency($concurrency)->setMaximumDepth($maxDepth)->setCrawlProfile(new CrawlInternalUrls($baseUrl))->startCrawling($baseUrl);
        $symfonyStyle->comment(Text::sprintf('COM_JCHOPTIMIZE_CLI_RECACHE_NUM_URLS_CRAWLED', $observer->getNumCrawled()));
        $symfonyStyle->success(Text::_('COM_JCHOPTIMIZE_CLI_RECACHE_SUCCESS'));

        return 0;
    }

    protected function configure(): void
    {
        $this->addOption('delete-cache', null, InputOption::VALUE_NONE | InputOption::VALUE_NEGATABLE, Text::_('COM_JCHOPTIMIZE_CLI_RECACHE_OPT_DELETE_CACHE'));
        $this->addOption('crawl-limit', 'l', InputOption::VALUE_REQUIRED, Text::_('COM_JCHOPTIMIZE_CLI_RECACHE_OPT_CRAWL_LIMIT'));
        $this->addOption('concurrency', 'c', InputOption::VALUE_REQUIRED, Text::_('COM_JCHOPTIMIZE_CLI_RECACHE_OPT_CONCURRENCY'));
        $this->addOption('max-depth', 'm', InputOption::VALUE_REQUIRED, Text::_('COM_JCHOPTIMIZE_CLI_RECACHE_OPT_MAX_DEPTH'));
        $this->setDescription(Text::_('COM_JCHOPTIMIZE_CLI_RECACHE_DESC'));
        $this->setHelp(Text::_('COM_JCHOPTIMIZE_CLI_RECACHE_HELP'));
    }
}
