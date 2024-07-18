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

namespace RegularLabs\Plugin\System\BetterPreview\Component\Zoo\Itemlist;

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
        if ($context != 'com_zoo.category.description')
        {
            return;
        }

        parent::render($article, $context);
    }

    public function states()
    {
        require_once JPATH_ADMINISTRATOR . '/components/com_zoo/config.php';

        $zoo = App::getInstance('zoo');

        $id = JFactory::getApplication()->input->get('category_id');

        $cat = $zoo->table->category->get($id);

        while ($cat)
        {
            $this->states[] = (object) [
                'table'     => 'zoo_category',
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
            $cat            = $cat->parent ? $zoo->table->category->get($cat->parent) : 0;
        }

        $this->setStates();
    }
}
