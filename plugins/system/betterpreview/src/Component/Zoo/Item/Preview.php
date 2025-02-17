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

namespace RegularLabs\Plugin\System\BetterPreview\Component\Zoo\Item;

defined('_JEXEC') or die;

use App;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Plugin\System\BetterPreview\Component\Preview as Main_Preview;

class Preview extends Main_Preview
{
    public function getShowIntro(&$article)
    {
        return 1;
    }

    public function renderPreview(&$article, $context)
    {
        if ($context != 'com_zoo.element.textarea')
        {
            return;
        }

        parent::render($article, $context);
    }

    public function restoreStates()
    {
        return;
    }

    public function setStates()
    {
        $app = App::getInstance('zoo');

        foreach ($this->states as $state)
        {
            $item = $app->table->item->get($state->id);

            if (empty($item))
            {
                continue;
            }

            if (isset($state->names->published))
            {
                $item->{$state->names->published} = 1;
            }

            if (isset($state->names->access))
            {
                $item->{$state->names->access} = 1;
            }

            if (isset($state->names->publish_up) && $state->publish_up > 0)
            {
                $item->{$state->names->publish_up} = $app->date->create(time() - 172800)->toSql();
            }

            if (isset($state->names->publish_down) && $state->publish_down > 0)
            {
                $item->{$state->names->publish_down} = $app->date->create(time() + 172800)->toSql();
            }
        }
    }

    public function states()
    {
        require_once JPATH_ADMINISTRATOR . '/components/com_zoo/config.php';

        $zoo = App::getInstance('zoo');

        $id = JFactory::getApplication()->input->get('item_id');

        $item = $zoo->table->item->get($id);
        $cats = $item->getRelatedCategories();

        $this->states[] = (object) [
            'table'        => 'zoo_item',
            'id'           => $item->id,
            'name'         => $item->name,
            'published'    => $item->state,
            'publish_up'   => $item->publish_up,
            'publish_down' => $item->publish_down,
            'access'       => $item->access,
            'hits'         => $item->hits,
            'url'          => $zoo->route->item($item, 0),
            'type'         => JText::_('ITEM'),
            'names'        => (object) [
                'id'           => 'id',
                'published'    => 'state',
                'publish_up'   => 'publish_up',
                'publish_down' => 'publish_down',
                'access'       => 'access',
                'hits'         => 'hits',
            ],
        ];

        foreach ($cats as $cat)
        {
            $this->states[] = (object) [
                'table'     => 'zoo_item',
                'id'        => $cat->id,
                'name'      => $cat->name,
                'published' => $cat->published,
                'url'       => $zoo->route->category($cat, 0),
                'type'      => JText::_('CATEGORY'),
                'names'     => (object) [
                    'id'        => 'id',
                    'published' => 'published',
                ],
            ];
        }

        $this->setStates();
    }
}
