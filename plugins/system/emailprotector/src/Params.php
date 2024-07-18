<?php
/**
 * @package         Email Protector
 * @version         5.2.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

namespace RegularLabs\Plugin\System\EmailProtector;

defined('_JEXEC') or die;

use RegularLabs\Library\ParametersNew as RL_Parameters;

class Params
{
    protected static $params  = null;
    protected static $regexes = null;

    public static function get()
    {
        if ( ! is_null(self::$params))
        {
            return self::$params;
        }

        $params = RL_Parameters::getPlugin('emailprotector');

        $params->id_pre  = substr(md5('a' . rand(1000, 9999)), 0, 4);
        $params->id_post = substr(md5('b' . rand(1000, 9999)), 0, 4);

        self::$params = $params;

        return self::$params;
    }

    public static function getRegex($type = 'email')
    {
        $regexes = self::getRegexes();

        return $regexes->{$type} ?? $regexes->tag;
    }

    private static function getRegexes()
    {
        if ( ! is_null(self::$regexes))
        {
            return self::$regexes;
        }

        self::$regexes = (object) [];

        // email@domain.com
        $email = '([\w\.\-\+]+\@\w[\w\.\-]*\.\w{2,20})';

        self::$regexes->email  = '(?<email>' . $email . ')';
        self::$regexes->simple = '[\w\.\-\+]\@\w';
        self::$regexes->js     = '<script[^>]*[^/]>.*?</script>';
        self::$regexes->injs   = '([\'"])(?<email>' . $email . ')\1';
        self::$regexes->link   = '<a\s+(?<link_pre>(?:[^>]*\s+)?)href\s*=\s*"mailto:(?<mailto>' . $email . '(?:%[^"]+)?(?:\?[^"]+)?)"(?<link_post>(?:\s+[^>]*)?)>(?<text>.*?)</a>';

        return self::$regexes;
    }
}
