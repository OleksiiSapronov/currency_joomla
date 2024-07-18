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

namespace JchOptimize\Joomla\Plugin;

use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;

abstract class PluginHelper extends JPluginHelper
{
    /**
     * Used to reset the plugins list after one has been modified to
     * force a reload from the database.
     */
    public static function reload(): void
    {
        static::$plugins = null;
    }
}
