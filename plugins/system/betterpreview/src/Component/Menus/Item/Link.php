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

namespace RegularLabs\Plugin\System\BetterPreview\Component\Menus\Item;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use MenusHelper;
use MenusModelMenutypes;
use RegularLabs\Plugin\System\BetterPreview\Component\Link as Main_Link;

class Link extends Main_Link
{
    var $types = [];

    public function getLinks()
    {
        if ( ! JFactory::getApplication()->input->get('id'))
        {
            return [];
        }

        $item = $this->getItem(
            JFactory::getApplication()->input->get('id'),
            'menu',
            ['name' => 'title', 'parent' => 'parent_id', 'url' => 'link', 'type' => 'type']
        );

        $parents = $this->getParents(
            $item,
            'menu',
            ['name' => 'title', 'parent' => 'parent_id', 'url' => 'link', 'type' => 'type'],
            [],
            1
        );

        $model = new MenusModelMenutypes;
        $model->getTypeOptions();
        $this->types = $model->getReverseLookup();

        $this->setParams($item);

        foreach ($parents as &$parent)
        {
            $this->setParams($parent);
        }

        return array_merge([$item], $parents);
    }

    private function getType($item)
    {
        $key = MenusHelper::getLinkKey($item->url);

        if (isset($this->types[$key]))
        {
            $item->type = JText::_($this->types[$key]);
        }

        return $item->type;
    }

    private function setParams(&$item)
    {
        if ($item->type == 'alias')
        {
            $name = $item->name;

            $db = JFactory::getDbo();

            $query = $db->getQuery(true)
                ->select('m.params')
                ->from('#__menu as m')
                ->where('m.id = ' . (int) $item->id);
            $db->setQuery($query);
            $params = json_decode($db->loadResult());

            if (is_null($params))
            {
                $params = (object) [];
            }

            $item = $this->getItem(
                $params->aliasoptions,
                'menu',
                ['name' => 'title', 'parent' => 'parent_id', 'url' => 'link', 'type' => 'type', 'home' => false]
            );

            $this->setParams($item);
            $item->name = $name . ' &rarr; ' . $item->name;
        }

        switch ($item->type)
        {
            case 'url':
                $item->type = JText::_('COM_MENUS_TYPE_EXTERNAL_URL');
                break;

            case 'separator':
                $item->type      = JText::_('COM_MENUS_TYPE_SEPARATOR');
                $item->url       = '';
                $item->published = 0;
                break;

            default:
                $item->type = $this->getType($item);
                $item->url  .= '&Itemid=' . $item->id;
                break;
        }
    }
}
