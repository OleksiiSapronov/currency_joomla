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

namespace RegularLabs\Plugin\System\BetterPreview\Component;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Plugin\System\BetterPreview\Params;

class Menu
{
    public static function getDefaultMenuItem(&$item)
    {
        $lang = $item->language ?? '';
        $menu = JFactory::getApplication()->getMenu('site');

        $default_menu_item = ! empty($menu) ? $menu->getDefault($lang) : null;

        if (empty($default_menu_item) && ! empty($menu))
        {
            $default_menu_item = $menu->getDefault();
        }

        return $default_menu_item;
    }

    public static function setItemId(&$item, $parent_menu_id = '')
    {
        $item->url = str_replace('&amp;', '&', $item->url);

        $default_menu_item = self::getDefaultMenuItem($item);

        if (empty($default_menu_item))
        {
            return;
        }

        $params = Params::get();

        // Return if url already contains an Itemid
        if (
            $params->default_menu_id != -1
            && strpos($item->url, '&Itemid=') !== false
        )
        {
            return;
        }

        // Link has no ItemId yet
        if (strpos($item->url, '&Itemid=') === false)
        {
            self::addItemId($item, $parent_menu_id);

            return;
        }

        // Replace the Itemid if it is the default (home) menu id
        $default_menu_url = $default_menu_item->link . '&Itemid=' . $default_menu_item->id;

        // Url is the home url, so leave Itemid alone
        if ($item->url == $default_menu_url)
        {
            return;
        }

        // Remove the home Itemid
        if ($params->default_menu_id == -1)
        {
            $item->url = RL_RegEx::replace('&Itemid=' . $default_menu_item->id . '$', '', $item->url);

            return;
        }

        // Default setting is to add the home id, so leave Itemid alone
        if ( ! $params->default_menu_id)
        {
            return;
        }

        // Replace the default home Itemid with the menu id set in the Better Preview settings
        $item->url = RL_RegEx::replace('&Itemid=' . $default_menu_item->id . '$', '&Itemid=' . $params->default_menu_id, $item->url);
    }

    private static function addItemId(&$item, $default_menu_id = '')
    {
        $params = Params::get();

        $item->menuid = Helper::getItemId($item->url);

        if ($item->menuid)
        {
            $item->url .= '&Itemid=' . $item->menuid;

            return;
        }

        // Add parent Itemid
        if ($default_menu_id)
        {
            $item->url .= '&Itemid=' . $default_menu_id;

            return;
        }

        // Don't add an Itemid
        if ($params->default_menu_id == -1)
        {
            return;
        }

        // Add a custom Itemid
        if ($params->default_menu_id)
        {
            $item->url .= '&Itemid=' . $params->default_menu_id;

            return;
        }

        // Add default menu id
        $default_menu_item = self::getDefaultMenuItem($item);

        if (empty($default_menu_item))
        {
            return;
        }

        $item->url .= '&Itemid=' . $default_menu_item->id;
    }
}
