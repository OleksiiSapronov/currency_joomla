<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Model;

use _JchOptimizeVendor\Joomla\DI\ContainerAwareInterface;
use _JchOptimizeVendor\Joomla\DI\ContainerAwareTrait;
use _JchOptimizeVendor\Laminas\Cache\Storage\StorageInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\TaggableInterface;
use JchOptimize\Core\Model\CacheModelTrait;
use JchOptimize\Core\PageCache\PageCache;

\defined('_JEXEC') or exit('Restricted Access');
class Cache implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    use CacheModelTrait;

    /**
     * @var StorageInterface
     */
    private $cache;

    /**
     * @var PageCache
     */
    private $pageCache;

    /**
     * @var StorageInterface
     */
    private $pageCacheStorage;

    /**
     * @var TaggableInterface
     */
    private $taggableCache;

    public function __construct(StorageInterface $cache, PageCache $pageCache, TaggableInterface $taggableCache)
    {
        $this->cache = $cache;
        $this->pageCache = $pageCache;
        $this->pageCacheStorage = $pageCache->getStorage();
        $this->taggableCache = $taggableCache;
    }
}
