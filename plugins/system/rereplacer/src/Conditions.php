<?php
/**
 * @package         ReReplacer
 * @version         13.2.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

namespace RegularLabs\Plugin\System\ReReplacer;

defined('_JEXEC') or die;

use RegularLabs\Library\Conditions as RL_Conditions;
use RegularLabs\Library\RegEx as RL_RegEx;

class Conditions
{
    public static function itemPass($item, $article = null)
    {
        $Conditions = RL_Conditions::getConditionsFromParams($item);
        $pass       = RL_Conditions::pass($Conditions, $item->match_method, $article);

        if ( ! $pass && $item->other_doreplace)
        {
            $item->replace = $item->other_replace;
            // replace \n with newline
            $item->replace = RL_RegEx::replace('(?<!\\\)\\\n', "\n", $item->other_replace);

            $pass = true;
        }

        return $pass ? $item : false;
    }
}
