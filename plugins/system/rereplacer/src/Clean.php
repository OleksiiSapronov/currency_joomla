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

class Clean
{
    public static function cleanString(&$string)
    {
        $string = str_replace(['[:space:]', '\[\:space\:\]', '[[space]]', '\[\[space\]\]'], ' ', $string);
        $string = str_replace(['[:comma:]', '\[\:comma\:\]', '[[comma]]', '\[\[comma\]\]'], ',', $string);
        $string = str_replace(['[:newline:]', '\[\:newline\:\]', '[[newline]]', '\[\[newline\]\]'], "\n", $string);
        $string = str_replace('[:REGEX_ENTER:]', '\\n', $string);
    }

    public static function cleanStringReplace(&$string, $is_regex = true)
    {
        if ( ! $is_regex)
        {
            $string = str_replace(['\\', '\\\\#', '$'], ['\\\\', '\\#', '\\$'], $string);
        }

        self::cleanString($string);
    }
}
