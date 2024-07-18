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
use JchOptimize\Core\Interfaces\MvcLoggerInterface;
use JchOptimize\Log\JoomlaLogger;
use Joomla\CMS\Log\Log;
use Psr\Log\LoggerInterface;

\defined('_JEXEC') or exit('Restricted access');
class LoggerProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->alias(MvcLoggerInterface::class, LoggerInterface::class)->share(LoggerInterface::class, function (): LoggerInterface {
            JoomlaLogger::addLogger(['text_file' => 'com_jchoptimize.logs.php'], Log::ALL, ['com_jchoptimize']);

            return JoomlaLogger::createDelegatedLogger();
        });
    }
}
