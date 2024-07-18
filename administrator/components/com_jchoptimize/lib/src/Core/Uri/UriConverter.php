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

namespace JchOptimize\Core\Uri;

use _JchOptimizeVendor\GuzzleHttp\Psr7\UriResolver;
use _JchOptimizeVendor\Psr\Http\Message\UriInterface;
use JchOptimize\Core\SystemUri;
use JchOptimize\Platform\Paths;

final class UriConverter
{
    public static function uriToFilePath(UriInterface $uri): string
    {
        $resolvedUri = UriResolver::resolve(SystemUri::currentUri(), $uri);
        $path = \str_replace(\JchOptimize\Core\Uri\Utils::originDomains(), Paths::rootPath().'/', (string) $resolvedUri->withQuery('')->withFragment(''));
        // convert all directory to unix style
        return \strtr(\rawurldecode($path), '\\', '/');
    }
}
