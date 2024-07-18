<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2021 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Service;

use _JchOptimizeVendor\Joomla\DI\Container;
use _JchOptimizeVendor\Joomla\DI\ServiceProviderInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Registry\Registry;

\defined('_JEXEC') or exit('Restricted access');
class ConfigurationProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->alias('params', Registry::class)->share(Registry::class, function (): Registry {
            // Get a clone so when we get a new instance of the container we get a different object
            $params = clone ComponentHelper::getParams('com_jchoptimize');
            if (!\defined('JCH_DEBUG')) {
                \define('JCH_DEBUG', $params->get('debug', 0));
            }

            return $params;
        });
    }
}
