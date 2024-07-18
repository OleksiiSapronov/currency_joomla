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

namespace RegularLabs\Plugin\System\BetterPreview\Component\K2\Category;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use K2HelperRoute;
use RegularLabs\Plugin\System\BetterPreview\Component\Helper as Main_Helper;

if ( ! class_exists('K2HelperRoute'))
{
    include_once JPATH_SITE . '/components/com_k2/helpers/route.php';
}

class Helper extends Main_Helper
{
    public static function getK2Category()
    {
        if ( ! JFactory::getApplication()->input->get('cid'))
        {
            return false;
        }

        $item = self::getItem(
            JFactory::getApplication()->input->get('cid'),
            'k2_categories',
            [],
            ['type' => 'K2_CATEGORY']
        );

        $item->url = K2HelperRoute::getCategoryRoute($item->id);

        return $item;
    }

    public static function getK2CategoryParents($item)
    {
        if (
            empty($item)
            || ! JFactory::getApplication()->input->get('cid')
        )
        {
            return false;
        }

        $parents = self::getParents(
            $item,
            'k2_categories',
            [],
            ['type' => 'K2_CATEGORY']
        );

        foreach ($parents as &$parent)
        {
            $parent->url = K2HelperRoute::getCategoryRoute($parent->id);
        }

        return $parents;
    }
}
