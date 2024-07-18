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
use RegularLabs\Plugin\System\BetterPreview\Component\Button as Main_Button;

class Button extends Main_Button
{
    public function getExtraJavaScript($text)
    {
        return '
                isjform = 0;
            ';
    }

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

        $item = $zoo->table->item->get($id);

        $url = $zoo->route->item($item, 0);

        if (strpos($url, 'item_id=') !== false)
        {
            return $url;
        }

        return $url . '&item_id=' . $id;
    }
}
