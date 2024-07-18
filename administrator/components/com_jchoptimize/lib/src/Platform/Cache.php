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

namespace JchOptimize\Platform;

use JchOptimize\ContainerFactory;
use JchOptimize\Core\Html\Processor;
use JchOptimize\Core\Interfaces\Cache as CacheInterface;
use JchOptimize\GetApplicationTrait;
use JchOptimize\Joomla\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\Event\Dispatcher;
use Joomla\Registry\Registry;

\defined('_JEXEC') or exit('Restricted Access');
class Cache implements CacheInterface
{
    use GetApplicationTrait;

    public static function cleanThirdPartyPageCache(): void
    {
        // Clean Joomla Cache
        $cache = Factory::getCache();
        $groups = ['page', 'pce'];
        foreach ($groups as $group) {
            $cache->clean($group);
        }
        // Clean LiteSpeed Cache
        if (\file_exists(\JPATH_PLUGINS.'/system/lscache/lscache.php')) {
            $dispatcher = new Dispatcher();
            $dispatcher->triggerEvent('onLSCacheExpired');
        }

        try {
            $app = self::getApplication();
            if (!$app->isClient('cli')) {
                $app->setHeader('X-LiteSpeed-Purge', '*');
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * @param array{headers: array{array-key: array{name:string, value:string}}, body:string}|null $data
     *
     * @return array{headers: array{array-key: array{name:string, value:string}}, body:string}|null
     */
    public static function prepareDataFromCache(?array $data): ?array
    {
        // The following code searches for a token in the cached page and replaces it with the proper token.
        if (isset($data['body'])) {
            $token = Session::getFormToken();
            $search = '#<input type="?hidden"? name="?[\\da-f]{32}"? value="?1"?\\s*/?>#';
            $replacement = '<input type="hidden" name="'.$token.'" value="1">';
            $data['body'] = \preg_replace($search, $replacement, $data['body']);
            $container = ContainerFactory::getNewContainerInstance();

            /** @var Processor $htmlProcessor */
            $htmlProcessor = $container->getNewInstance(Processor::class);
            $htmlProcessor->setHtml($data['body']);
            $htmlProcessor->processDataFromCacheScriptToken($token);
            $data['body'] = $htmlProcessor->getHtml();
        }

        return $data;
    }

    /**
     * @param array{headers:array<array-key, array{name:string, value:string}>, body:string} $data
     */
    public static function outputData(array $data): void
    {
        $app = self::getApplication();
        if (!empty($data['headers'])) {
            foreach ($data['headers'] as $header) {
                $app->setHeader($header['name'], $header['value']);
            }
        }
        $app->setBody($data['body']);
        echo $app->toString((bool) $app->get('gzip'));
        $app->close();
    }

    public static function isPageCacheEnabled(Registry $params, bool $nativeCache = \false): bool
    {
        $integratedPageCache = 'jchoptimizepagecache';
        if (!$nativeCache) {
            /** @var string $integratedPageCache */
            $integratedPageCache = $params->get('pro_page_cache_integration', 'jchoptimizepagecache');
        }

        return PluginHelper::isEnabled('system', $integratedPageCache);
    }

    public static function getCacheNamespace(bool $pageCache = \false): string
    {
        if ($pageCache) {
            return 'jchoptimizepagecache';
        }

        return 'jchoptimizecache';
    }

    public static function isCaptureCacheIncompatible(): bool
    {
        return \false;
    }
}
