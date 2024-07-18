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

use _JchOptimizeVendor\Laminas\Cache\Exception\ExceptionInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\ClearByNamespaceInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\IterableInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\StorageInterface;
use _JchOptimizeVendor\Psr\Http\Message\UriInterface;
use _JchOptimizeVendor\Spatie\Crawler\CrawlQueues\CrawlQueue;
use _JchOptimizeVendor\Spatie\Crawler\CrawlUrl;
use _JchOptimizeVendor\Spatie\Crawler\Exceptions\InvalidUrl;
use _JchOptimizeVendor\Spatie\Crawler\Exceptions\UrlNotFoundByIndex;

class CacheCrawlQueue implements CrawlQueue
{
    protected const URLS_NAMESPACE = 'jchoptimizeurls';
    protected const PENDING_URLS_NAMESPACE = 'jchoptimizependingurls';

    /**
     * @var ClearByNamespaceInterface&IterableInterface&StorageInterface
     */
    protected StorageInterface $storage;

    /**
     * @var ClearByNamespaceInterface&IterableInterface&StorageInterface
     */
    protected StorageInterface $pendingStorage;

    /**
     * @param ClearByNamespaceInterface&IterableInterface&StorageInterface $storage
     * @param ClearByNamespaceInterface&IterableInterface&StorageInterface $pendingStorage
     */
    public function __construct(StorageInterface $storage, StorageInterface $pendingStorage)
    {
        $this->storage = $storage;
        $this->storage->getOptions()->setNamespace(self::URLS_NAMESPACE);
        $this->pendingStorage = $pendingStorage;
        $this->pendingStorage->getOptions()->setNamespace(self::PENDING_URLS_NAMESPACE);
    }

    public function __destruct()
    {
        $this->storage->clearByNameSpace(self::URLS_NAMESPACE);
        $this->pendingStorage->clearByNameSpace(self::PENDING_URLS_NAMESPACE);
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidUrl
     */
    public function add(CrawlUrl $url): CrawlQueue
    {
        $urlId = $this->getUrlId($url);
        $url->setId($urlId);
        $this->storage->addItem($urlId, $url);
        $this->pendingStorage->addItem($urlId, $url);

        return $this;
    }

    /**
     * @param CrawlUrl|UriInterface $crawlUrl
     *
     * @throws ExceptionInterface
     * @throws InvalidUrl
     */
    public function has($crawlUrl): bool
    {
        if ($crawlUrl instanceof CrawlUrl || $crawlUrl instanceof UriInterface) {
            return $this->storage->hasItem($this->getUrlId($crawlUrl));
        }

        throw InvalidUrl::unexpectedType($crawlUrl);
    }

    /**
     * @throws \Exception
     */
    public function hasPendingUrls(): bool
    {
        return (bool) \iterator_count($this->pendingStorage->getIterator());
    }

    /**
     * @param string $id
     *
     * @throws ExceptionInterface
     */
    public function getUrlById($id): CrawlUrl
    {
        $result = $this->storage->getItem($id, $success);
        if (!$success) {
            throw new UrlNotFoundByIndex("Crawl url with id {$id} not found in cache");
        }

        return $result;
    }

    /**
     * @throws ExceptionInterface
     * @throws \Exception
     */
    public function getPendingUrl(): ?CrawlUrl
    {
        /** @var string[] $iterator */
        $iterator = $this->pendingStorage->getIterator();
        foreach ($iterator as $item) {
            /** @var CrawlUrl $uri */
            return $this->pendingStorage->getItem($item);
        }

        return null;
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidUrl
     */
    public function hasAlreadyBeenProcessed(CrawlUrl $url): bool
    {
        $id = $this->getUrlId($url);
        if ($this->pendingStorage->hasItem($id)) {
            return \false;
        }
        if ($this->storage->hasItem($id)) {
            return \true;
        }

        return \false;
    }

    /**
     * @throws ExceptionInterface
     * @throws InvalidUrl
     */
    public function markAsProcessed(CrawlUrl $crawlUrl): void
    {
        $id = $this->getUrlId($crawlUrl);
        $this->pendingStorage->removeItem($id);
    }

    /**
     * @throws \Exception
     */
    public function getProcessedUrlCount(): int
    {
        return \iterator_count($this->storage->getIterator()) - \iterator_count($this->pendingStorage->getIterator());
    }

    /**
     * @param CrawlUrl|UriInterface $crawlUrl
     *
     * @throws InvalidUrl
     */
    protected function getUrlId($crawlUrl): string
    {
        if ($crawlUrl instanceof CrawlUrl) {
            return \md5((string) $crawlUrl->url);
        }
        if ($crawlUrl instanceof UriInterface) {
            return \md5((string) $crawlUrl);
        }

        throw InvalidUrl::unexpectedType($crawlUrl);
    }
}
