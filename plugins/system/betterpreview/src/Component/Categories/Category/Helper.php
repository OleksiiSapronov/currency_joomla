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

namespace RegularLabs\Plugin\System\BetterPreview\Component\Categories\Category;

defined('_JEXEC') or die;

use ContentHelperRoute;
use Joomla\CMS\Factory as JFactory;
use RegularLabs\Plugin\System\BetterPreview\Component\Helper as Main_Helper;

if ( ! class_exists('ContentHelperRoute'))
{
    require_once JPATH_SITE . '/components/com_content/helpers/route.php';
}

class Helper extends Main_Helper
{
    public static function getCategory()
    {
        if (
            JFactory::getApplication()->input->get('extension', 'com_content') != 'com_content'
            || ! JFactory::getApplication()->input->get('id')
        )
        {
            return false;
        }

        $item = parent::getItem(
            JFactory::getApplication()->input->get('id'),
            'categories',
            ['name' => 'title', 'parent' => 'parent_id', 'language' => 'language'],
            ['type' => 'JCATEGORY']
        );

        $item->url = ContentHelperRoute::getCategoryRoute($item->id, $item->language);

        return $item;
    }

    public static function getCategoryParents($item)
    {
        if (
            empty($item)
            || JFactory::getApplication()->input->get('extension', 'com_content') != 'com_content'
            || ! JFactory::getApplication()->input->get('id')
        )
        {
            return false;
        }

        $parents = parent::getParents(
            $item,
            'categories',
            ['name' => 'title', 'parent' => 'parent_id', 'language' => 'language'],
            ['type' => 'JCATEGORY'],
            true
        );

        foreach ($parents as &$parent)
        {
            $parent->url = ContentHelperRoute::getCategoryRoute($parent->id, $item->language);
        }

        return $parents;
    }
}
