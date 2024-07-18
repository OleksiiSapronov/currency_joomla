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

namespace JchOptimize;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\ConsoleApplication;
use Joomla\CMS\Factory;

trait GetApplicationTrait
{
    /**
     * @return CMSApplication|ConsoleApplication
     */
    protected static function getApplication()
    {
        $app = null;

        try {
            $app = Factory::getApplication();
        } catch (\Exception $e) {
        }
        \assert($app instanceof CMSApplication || $app instanceof ConsoleApplication);

        return $app;
    }
}
