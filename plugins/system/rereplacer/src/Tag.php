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

use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\RegEx as RL_RegEx;

class Tag
{
    public static function getAreaByType(&$string, $area_type = '')
    {
        if ( ! $string || ! $area_type)
        {
            return [];
        }

        self::tagAreaByType($string, $area_type);

        $start = '<!-- START: RR_' . strtoupper($area_type) . ' -->';
        $end   = '<!-- END: RR_' . strtoupper($area_type) . ' -->';

        $matches = explode($start, $string);
        array_shift($matches);

        foreach ($matches as $i => $match)
        {
            $orig = $start . $match;
            $text = $match;

            if (strpos($text, $end) !== false)
            {
                $text = substr($text, 0, strrpos($text, $end));
                $orig = $start . $text . $end;
            }

            $matches[$i] = [$orig, $text];
        }

        return $matches;
    }

    public static function tagArea(&$string, $area_type = '')
    {
        if ( ! $string || ! $area_type)
        {
            return false;
        }

        $start = '<!-- START: RR_' . strtoupper($area_type) . ' -->';
        $end   = '<!-- END: RR_' . strtoupper($area_type) . ' -->';

        $string = $start . $string . $end;

        if ($area_type == 'article_text')
        {
            $string = RL_RegEx::replace('(<hr class="system-pagebreak".*?>)', $end . '\1' . $start, $string);
        }

        return true;
    }

    private static function tagAreaByType(&$string, $area_type)
    {
        switch ($area_type)
        {
            case 'component':
                self::tagComponent($string);
                break;
            case 'body':
                self::tagBody($string);
                break;
            case 'head':
                self::tagHead($string);
                break;
        }
    }

    private static function tagBody(&$string)
    {
        if ( ! $string)
        {
            return;
        }

        $start = '<!-- START: RR_BODY -->';

        if (strpos($string, $start) !== false)
        {
            return;
        }

        if (strpos($string, '<body') !== false && strpos($string, '</body>') !== false)
        {
            self::tagByTagType($string, 'body', 'body');

            return;
        }

        if (strpos($string, '<item') !== false && strpos($string, '</item>') !== false)
        {
            self::tagByTagType($string, 'item', 'body');

            return;
        }

        self::tagArea($string, 'body');
    }

    private static function tagByTagType(&$string, $tag, $area_type)
    {
        if ( ! $string)
        {
            return;
        }

        $start = '<!-- START: RR_' . strtoupper($area_type) . ' -->';
        $end   = '<!-- END: RR_' . strtoupper($area_type) . ' -->';

        $string = RL_RegEx::replace('(<' . $tag . '(\s[^>]*)?>)', '\1' . $start, $string);
        $string = str_replace('</' . $tag . '>', $end . '</' . $tag . '>', $string);
    }

    private static function tagComponent(&$string)
    {
        if ( ! $string)
        {
            return;
        }

        $start = '<!-- START: RR_COMPONENT -->';

        if (RL_Document::isFeed())
        {
            self::tagByTagType($string, 'item', 'component');
        }

        if (strpos($string, $start) === false)
        {
            self::tagArea($string, 'component');
        }
    }

    private static function tagHead(&$string)
    {
        if ( ! $string)
        {
            return;
        }

        if (strpos($string, '</head>') === false)
        {
            return;
        }

        self::tagByTagType($string, 'head', 'head');
    }
}
