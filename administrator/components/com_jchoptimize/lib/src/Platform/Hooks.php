<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Platform;

use JchOptimize\Core\Interfaces\Hooks as HooksInterface;
use JchOptimize\Joomla\Plugin\PluginHelper;
use Joomla\CMS\Factory;

\defined('_JEXEC') or exit('Restricted Access');
class Hooks implements HooksInterface
{
    public static function onPageCacheSetCaching(): bool
    {
        /** @var array<array-key, mixed> $results */
        $results = [];

        try {
            $results = Factory::getApplication()->triggerEvent('onPageCacheSetCaching');
        } catch (\Exception $e) {
        }

        return !\in_array(\false, $results, \true);
    }

    public static function onPageCacheGetKey(array $parts): array
    {
        try {
            $results = Factory::getApplication()->triggerEvent('onPageCacheGetKey');
        } catch (\Exception $e) {
        }
        if (!empty($results)) {
            $parts = \array_merge($parts, $results);
        }

        return $parts;
    }

    public static function onUserPostForm(): void
    {
        try {
            // Import the user plugin group.
            PluginHelper::importPlugin('user');
            Factory::getApplication()->triggerEvent('onUserPostForm');
        } catch (\Exception $e) {
        }
    }

    public static function onUserPostFormDeleteCookie(): void
    {
        try {
            // Import the user plugin group.
            PluginHelper::importPlugin('user');
            Factory::getApplication()->triggerEvent('onUserPostFormDeleteCookie');
        } catch (\Exception $e) {
        }
    }

    public static function onHttp2GetPreloads(array $preloads): array
    {
        return $preloads;
    }
}
