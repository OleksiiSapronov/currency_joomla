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

use JchOptimize\Core\Interfaces\Profiler as ProfilerInterface;

\defined('_JEXEC') or exit('Restricted access');
class Profiler implements ProfilerInterface
{
    /**
     * @param string $html
     * @param bool   $isAmpPage
     */
    public static function attachProfiler(&$html, $isAmpPage = \false)
    {
    }

    /**
     * @param string $text
     * @param bool   $mark
     */
    public static function start($text, $mark = \false)
    {
        if ($mark) {
            self::mark('before'.$text);
        }
    }

    /**
     * @param string $text
     */
    public static function mark($text)
    {
        \JProfiler::getInstance('Application')->mark($text.' plgSystem (JCH Optimize)');
    }

    /**
     * @param string $text
     * @param bool   $mark
     */
    public static function stop($text, $mark = \false)
    {
        if ($mark) {
            self::mark('after'.$text);
        }
    }
}
