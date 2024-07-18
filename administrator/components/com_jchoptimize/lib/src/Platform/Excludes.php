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

namespace JchOptimize\Platform;

use JchOptimize\Core\Interfaces\Excludes as ExcludesInterface;

\defined('_JEXEC') or exit('Restricted access');
class Excludes implements ExcludesInterface
{
    public static function body(string $type, string $section = 'file'): array
    {
        if ('js' == $type) {
            if ('script' == $section) {
                return [['script' => 'var mapconfig90'], ['script' => 'var addy']];
            }

            return [['url' => 'assets.pinterest.com/js/pinit.js']];
        }
        if ('css' == $type) {
            return [];
        }

        return [];
    }

    public static function extensions(): string
    {
        // language=RegExp
        return '(?>components|modules|plugins/[^/]+|media(?!/system|/jui|/cms|/media|/css|/js|/images|/vendor)(?:/vendor)?)/';
    }

    public static function head(string $type, string $section = 'file'): array
    {
        if ('js' == $type) {
            if ('script' == $section) {
                return [];
            }

            return [['url' => 'plugin_googlemap3'], ['url' => '/jw_allvideos/'], ['url' => '/tinymce/']];
        }
        if ('css' == $type) {
            return [];
        }

        return [];
    }

    public static function editors(string $url): bool
    {
        return (bool) \preg_match('#/editors/#i', $url);
    }

    public static function smartCombine(): array
    {
        return ['media/(?:jui|system|cms)/', '/templates/', '.'];
    }
}
