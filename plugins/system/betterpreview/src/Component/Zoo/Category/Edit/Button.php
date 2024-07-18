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
use RegularLabs\Plugin\System\BetterPreview\Component\Button as Main_Button;

class Button extends Main_Button
{
    public function getURL($name)
    {
        $id = JFactory::getApplication()->input->get('cid', [0], 'array');
        $id = (int) $id[0];

        if ( ! $id)
        {
            return false;
        }

        require_once JPATH_ADMINISTRATOR . '/components/com_zoo/config.php';

        $zoo = App::getInstance('zoo');

        $item = $zoo->table->category->get($id);

        return $zoo->route->category($item, 0);
    }
}
