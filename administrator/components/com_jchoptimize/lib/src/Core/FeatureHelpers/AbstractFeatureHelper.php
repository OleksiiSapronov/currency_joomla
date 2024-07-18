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
use _JchOptimizeVendor\Joomla\DI\ContainerAwareInterface;
use _JchOptimizeVendor\Joomla\DI\ContainerAwareTrait;
use Joomla\Registry\Registry;

\defined('_JCH_EXEC') or exit('Restricted access');
class AbstractFeatureHelper implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected Registry $params;

    public function __construct(Container $container, Registry $params)
    {
        $this->container = $container;
        $this->params = $params;
    }
}
