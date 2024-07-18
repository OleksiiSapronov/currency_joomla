<?php
/**
 * @package         Advanced Module Manager
 * @version         9.9.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

namespace RegularLabs\Plugin\System\AdvancedModules;

defined('_JEXEC') or die;

use RegularLabs\Library\ParametersNew as RL_Parameters;
use RegularLabs\Library\PluginTag as RL_PluginTag;
use RegularLabs\Library\RegEx as RL_RegEx;

class Params
{
    protected static $params = null;

    public static function get()
    {
        if ( ! is_null(self::$params))
        {
            return self::$params;
        }

        $params = RL_Parameters::getComponent('advancedmodules');

        self::$params = $params;

        return self::$params;
    }

    public static function getRegex($get_surrounding = false)
    {
        $params = self::get();

        // Tag character start and end
        [$tag_start, $tag_end] = self::getTagCharacters();
        $tag_start = RL_RegEx::quote($tag_start);
        $tag_end   = RL_RegEx::quote($tag_end);

        $tags  = RL_RegEx::quote([$params->tag_remove_modules, $params->tag_remove_module, $params->tag_remove_modulepos], 'type');
        $regex = $tag_start
            . $tags
            . ' (?<attributes>.*?)'
            . $tag_end;

        if ( ! $get_surrounding)
        {
            return $regex;
        }

        $pre  = RL_PluginTag::getRegexSurroundingTagsPre();
        $post = RL_PluginTag::getRegexSurroundingTagsPost();

        return $pre . $regex . $post;
    }

    public static function getTagCharacters()
    {
        $params = self::get();

        if ( ! isset($params->tag_character_start))
        {
            self::setTagCharacters();
        }

        return [$params->tag_character_start, $params->tag_character_end];
    }

    public static function setTagCharacters()
    {
        $params = self::get();

        [self::$params->tag_character_start, self::$params->tag_character_end] = explode('.', $params->tag_characters);
    }
}
