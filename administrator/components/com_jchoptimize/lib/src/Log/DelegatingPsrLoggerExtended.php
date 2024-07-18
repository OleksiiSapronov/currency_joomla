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

namespace JchOptimize\Log;

use Joomla\CMS\Log\DelegatingPsrLogger;

\defined('_JEXEC') or exit('Restricted Access');

/**
 * @psalm-suppress all
 */
class DelegatingPsrLoggerExtended extends DelegatingPsrLogger
{
    public function log($level, $message, array $context = [])
    {
        $context = \array_merge($context, ['category' => 'com_jchoptimize']);
        parent::log($level, $message, $context);
    }
}
