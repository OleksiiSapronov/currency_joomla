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

namespace RegularLabs\Plugin\System\BetterPreview\Component\Zoo\Category\Edit;

defined('_JEXEC') or die;

use App;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Plugin\System\BetterPreview\Component\Link as Main_Link;

class Link extends Main_Link
{
    public function getLinks()
    {
        $id = JFactory::getApplication()->input->get('cid', [0], 'array');
        $id = (int) $id[0];

        if ( ! $id)
        {
            return [];
        }

        require_once JPATH_ADMINISTRATOR . '/components/com_zoo/config.php';

        $zoo = App::getInstance('zoo');

        $items = [];

        $cat = $zoo->table->category->get($id);

        while ($cat)
        {
            $items[] = (object) [
                'id'        => $cat->id,
                'name'      => $cat->name,
                'published' => $cat->published,
                'url'       => $zoo->route->category($cat, 0),
                'type'      => JText::_('CATEGORY'),
            ];

            $cat = $cat->parent ? $zoo->table->category->get($cat->parent) : 0;
        }

        return $items;
    }
}
