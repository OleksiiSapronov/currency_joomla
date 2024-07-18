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

namespace JchOptimize\Core\FeatureHelpers;

use _JchOptimizeVendor\Joomla\DI\Container;
use _JchOptimizeVendor\Psr\Http\Message\UriInterface;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Http2Preload;
use JchOptimize\Core\Uri\Utils;
use Joomla\Registry\Registry;

\defined('_JCH_EXEC') or exit('Restricted access');
class Http2Excludes extends \JchOptimize\Core\FeatureHelpers\AbstractFeatureHelper
{
    private Http2Preload $http2Preload;

    public function __construct(Container $container, Registry $params, Http2Preload $http2Preload)
    {
        parent::__construct($container, $params);
        $this->http2Preload = $http2Preload;
    }

    public function addHttp2Includes(): void
    {
        /** @var string[] $aIncludeFiles */
        $aIncludeFiles = $this->params->get('pro_http2_include', []);
        if (empty($aIncludeFiles)) {
            return;
        }
        foreach ($aIncludeFiles as $sIncludeFile) {
            \preg_match('#\\.\\K(?:js|css|webp|gif|jpe?g|png|woff2?|ttf)(?=$|[\\#?])#i', $sIncludeFile, $aM);

            switch ($aM[0]) {
                case 'js':
                    $type = 'script';

                    break;

                case 'css':
                    $type = 'style';

                    break;

                case 'woff':
                case 'woff2':
                case 'ttf':
                    $type = 'font';

                    break;

                case 'webp':
                case 'gif':
                case 'jpg':
                case 'jpeg':
                case 'png':
                    $type = 'image';

                    break;

                default:
                    $type = '';

                    break;
            }
            if ('' != $type) {
                $this->http2Preload->addAdditional(Utils::uriFor($sIncludeFile), $type, $aM[0]);
            }
        }
    }

    public function findHttp2Excludes(UriInterface $uri, bool $bDeferred): bool
    {
        if (Helper::findExcludes($this->params->get('pro_http2_exclude', []), (string) $uri)) {
            return \true;
        }
        // If file is marked deferred when 'Exclude deferred' is enabled, return
        if ($this->params->get('pro_http2_exclude_deferred', '1') && $bDeferred) {
            return \true;
        }

        return \false;
    }
}
