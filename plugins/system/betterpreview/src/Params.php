<?php
/**
 * @package         Better Preview
 * @version         6.9.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

namespace RegularLabs\Plugin\System\BetterPreview;

defined('_JEXEC') or die;

use RegularLabs\Library\ParametersNew as RL_Parameters;

class Params
{
    protected static $params = null;

    public static function get()
    {
        if ( ! is_null(self::$params))
        {
            return self::$params;
        }

        self::$params = RL_Parameters::getPlugin('betterpreview');

        return self::$params;
    }
}
