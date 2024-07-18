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

namespace JchOptimize\Service;

use _JchOptimizeVendor\Joomla\DI\Container;
use _JchOptimizeVendor\Joomla\DI\ServiceProviderInterface;
use _JchOptimizeVendor\Spatie\Crawler\CrawlQueues\CrawlQueue;
use JchOptimize\Model\ReCache;
use JchOptimize\Model\ReCacheCliJ3;
use Joomla\Registry\Registry;

class ReCacheProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->share(ReCache::class, function (Container $container): ReCache {
            return new ReCache($container->get(Registry::class), $container->get(CrawlQueue::class));
        });
        $container->share(ReCacheCliJ3::class, function (Container $container): ReCacheCliJ3 {
            return new ReCacheCliJ3($container->get(Registry::class), $container->get(CrawlQueue::class));
        });
    }
}
