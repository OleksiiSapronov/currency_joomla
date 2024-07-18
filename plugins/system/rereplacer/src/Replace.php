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

use RegularLabs\Library\ArrayHelper as RL_Array;
use RegularLabs\Library\RegEx as RL_RegEx;

/**
 * Plugin that replaces stuff
 */
class Replace
{
    static $article  = null;
    static $counter  = [];
    static $item     = null;
    static $php_vars = [];
    static $splitter = '<!-- RR_REPLACE_SPLITTER -->';

    public static function replace(&$string, $item = null, $article = null)
    {
        if (empty($string))
        {
            return;
        }

        if ( ! empty($item))
        {
            self::$item = clone($item);
        }

        self::$article = $article;

        if ( ! empty($article))
        {
            self::$php_vars['article'] = $article;
        }

        if (is_array($string))
        {
            self::replaceArray($string);

            return;
        }

        self::replaceItemInString($string);
    }

    public static function replaceInAreas(&$string)
    {
        if ( ! is_string($string) || $string == '')
        {
            return;
        }

        self::replaceInArea($string, 'component');
        self::replaceInArea($string, 'head');
        self::replaceInArea($string, 'body');

        self::replaceEverywhere($string);
    }

    public static function replaceInArray(&$array)
    {
        foreach ($array as $key => &$string)
        {
            // only do something if string is not empty
            // or on uneven count = not yet protected
            if (trim($string) == '' || fmod($key, 2))
            {
                continue;
            }

            self::replacer($string);
        }
    }

    private static function getCounterName($search, $replace)
    {
        // Counter is used to make it possible to use \# or [[counter]] in the replacement to refer to the incremental counter
        $counter_name = base64_encode($search . $replace);

        if ( ! isset(self::$counter[$counter_name]))
        {
            self::$counter[$counter_name] = 0;
        }

        return $counter_name;
    }

    private static function getPhpResult($string)
    {
        self::preparePhp($string);

        return Code::run($string, self::$php_vars);
    }

    private static function preparePhp(&$string)
    {
        $string = trim($string);
        $string = str_replace('?><?php', '', $string . '<?php ;');

        if (substr($string, 0, 5) !== '<?php')
        {
            $string = '?>' . $string;

            return;
        }

        $string = substr($string, 5);
    }

    private static function prepareRegex(&$string, $dotall = true, $casesensitive = true)
    {
        $string = '#' . $string . '#';

        $string .= $dotall ? 's' : ''; // . (dot) also matches newlines
        $string .= $casesensitive ? '' : 'i'; // case-insensitive pattern matching

        // replace new lines with regex match
        $string = str_replace(["\r", "\n"], ['', '(?:\r\n|\r|\n)'], $string);
    }

    private static function replaceArray(&$array)
    {
        if ( ! is_array($array))
        {
            return;
        }

        foreach ($array as &$string)
        {
            self::replace($string);
        }
    }

    private static function replaceEverywhere(&$string)
    {
        if ( ! is_string($string) || $string == '')
        {
            return;
        }

        $items = Items::getItemList('everywhere');

        self::replaceItemList($string, $items);
    }

    private static function replaceInArea(&$string, $area_type = '')
    {
        if ( ! is_string($string) || $string == '' || ! $area_type)
        {
            return;
        }

        $items = Items::getItemList($area_type);

        if (empty($items))
        {
            return;
        }

        $areas = Tag::getAreaByType($string, $area_type);

        foreach ($areas as $area)
        {
            $orig = $area[0];
            $text = $area[0];

            self::replaceItemList($text, $items);

            $string = str_replace($orig, $text, $string);
        }

        unset($areas);
    }

    private static function replaceItemInString(&$string)
    {
        switch (self::$item->regex)
        {
            case true:
                self::replaceRegEx($string);
                break;

            default:
                self::replaceString($string);
                break;
        }

        if (self::$item->treat_as_php)
        {
            self::replacePhp($string);
        }
    }

    private static function replaceItemList(&$string, $items)
    {
        if (empty($items))
        {
            return;
        }

        if ( ! is_array($items))
        {
            $items = [$items];
        }

        foreach ($items as $item)
        {
            self::replace($string, $item);
        }
    }

    private static function replaceOccurrence($search, $replace, &$string, $count = 0, $counter_name = '')
    {
        if ( ! $counter_name)
        {
            $string = RL_RegEx::replace($search, $replace, $string, '', $count ?: -1);

            return;
        }

        for ($i = 0; $i < $count; $i++)
        {
            // Replace \# with the incremental counter
            $replace_c = str_replace(['\#', '[[counter]]'], ++self::$counter[$counter_name], $replace);

            // Replace with offset
            RL_RegEx::match(self::$item->search, $string, $matches, null, PREG_OFFSET_CAPTURE);

            $substring          = substr($string, $matches[0][1]);
            $substring_replaced = RL_RegEx::replaceOnce($search, $replace_c, $substring);

            $string = str_replace($substring, $substring_replaced, $string);
        }
    }

    private static function replacePhp(&$string)
    {
        if (strpos($string, '<!-- >>> ReReplacer: START PHP >>> -->') === false)
        {
            return;
        }

        $regex = '<\!-- >>> ReReplacer: START PHP >>> -->(.*?)<\!-- <<< ReReplacer: END PHP <<< -->';

        RL_RegEx::matchAll($regex, $string, $matches);

        if (empty($matches))
        {
            return;
        }

        foreach ($matches as $match)
        {
            $result = self::getPhpResult($match[1]);
            $string = str_replace($match[0], $result, $string);
        }
    }

    private static function replaceRegEx(&$string)
    {
        $string       = str_replace(chr(194) . chr(160), ' ', $string);
        $string_array = Protect::stringToProtectedArray($string, self::$item);

        Clean::cleanString(self::$item->search);

        // escape hashes
        self::$item->search = str_replace('#', '\#', self::$item->search);
        // unescape double escaped hashes
        self::$item->search = str_replace('\\\\#', '\#', self::$item->search);

        if (self::$item->strip_p_tags)
        {
            self::$item->search = '(?:<p(?: [^>]*)?>)?' . self::$item->search . '(?:</p>)?';
        }

        self::prepareRegex(self::$item->search, self::$item->s_modifier, self::$item->casesensitive);

        self::replaceInArray($string_array);

        $string = implode('', $string_array);
    }

    private static function replaceString(&$string)
    {
        $string_array = Protect::stringToProtectedArray($string, self::$item);

        $search_array  = [self::$item->search];
        $replace_array = [self::$item->replace];

        if (self::$item->treat_as_list)
        {
            $search_array  = RL_Array::toArray(self::$item->search, ',', false, false);
            $replace_array = self::$item->replace == '' ? [''] : RL_Array::toArray(self::$item->replace, ',', false, false);
        }

        $replace_count = count($replace_array);

        foreach ($search_array as $key => $search)
        {
            // remove any leading new line characters, but keep other whitespace
            $search = ltrim($search, "\r\n");

            if ($search == '')
            {
                continue;
            }

            // Prepare search string
            Clean::cleanString($search);
            self::$item->search = RL_RegEx::quote($search);

            if (self::$item->word_search)
            {
                self::$item->search = '(?<!\p{L})(' . self::$item->search . ')(?!\p{L})';
            }

            if (self::$item->strip_p_tags)
            {
                self::$item->search = '(?:<p(?: [^>]*)?>)?' . self::$item->search . '(?:</p>)?';
            }

            self::prepareRegex(self::$item->search, true, self::$item->casesensitive);

            // Prepare replace string
            self::$item->replace = ($replace_count > $key) ? $replace_array[$key] : $replace_array[0];
            // remove any leading new line characters, but keep other whitespace
            self::$item->replace = ltrim(self::$item->replace, "\r\n");

            self::replaceInArray($string_array);
        }

        $string = implode('', $string_array);
    }

    private static function replacer(&$string)
    {
        if ( ! RL_RegEx::match(self::$item->search, $string))
        {
            return;
        }

        Variables::replacePre(self::$item->replace, self::$article);

        // Get the resulting html if treat as PHP is on
        if (self::$item->treat_as_php)
        {
            self::$item->replace = '<!-- >>> ReReplacer: START PHP >>> -->' . self::$item->replace . '<!-- <<< ReReplacer: END PHP <<< -->';
        }

        Clean::cleanStringReplace(self::$item->replace, self::$item->regex);

        if (self::$item->max_replacements)
        {
            self::$item->thorough = false;
        }

        // Do a simple replace if not thorough, not using a max and counter is not found
        if ( ! self::$item->thorough
            && ! self::$item->max_replacements
            && strpos(self::$item->replace, '[[counter]]') === false
            && strpos(self::$item->replace, '\#') === false
        )
        {
            $string = RL_RegEx::replace(self::$item->search, self::$splitter . self::$item->replace . self::$splitter, $string, '', self::$item->max_replacements ?: -1);

            Variables::replacePost($string, self::$splitter);

            return;
        }

        $counter_name = self::getCounterName(self::$item->search, self::$item->replace);

        $thorough_count = 1; // prevents the thorough search to repeat endlessly
        while ($count = RL_RegEx::matchAll(self::$item->search, $string))
        {
            if (self::$item->max_replacements > 0 && self::$counter[$counter_name] >= self::$item->max_replacements)
            {
                break;
            }

            if (self::$item->max_replacements > 0 && (self::$counter[$counter_name] + $count) > self::$item->max_replacements)
            {
                $count = self::$item->max_replacements - self::$counter[$counter_name];
            }

            self::replaceOccurrence(self::$item->search, self::$splitter . self::$item->replace . self::$splitter, $string, $count, $counter_name);

            Variables::replacePost($string, self::$splitter);

            if ( ! self::$item->thorough)
            {
                break;
            }

            if (++$thorough_count >= 100)
            {
                break;
            }
        }
    }
}
