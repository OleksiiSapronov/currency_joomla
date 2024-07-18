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

use JchOptimize\Core\Helper;

\defined('_JCH_EXEC') or exit('Restricted access');
class DynamicSelectors extends \JchOptimize\Core\FeatureHelpers\AbstractFeatureHelper
{
    /**
     * @param string[] $matches
     */
    public function getDynamicSelectors(array $matches): bool
    {
        // Add all CSS containing any specified dynamic CSS to the critical CSS
        $dynamicSelectors = Helper::getArray($this->params->get('pro_dynamic_selectors', []));
        $dynamicSelectors = \array_unique(\array_merge($dynamicSelectors, ['offcanvas', 'off-canvas', 'mobilemenu', 'mobile-menu', '.jch-lazyloaded']));
        foreach ($dynamicSelectors as $dynamicSelector) {
            if (\false !== \strpos($matches[2], $dynamicSelector)) {
                return \true;
            }
        }

        return \false;
    }
}
