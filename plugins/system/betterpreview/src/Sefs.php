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

namespace RegularLabs\Plugin\System\BetterPreview;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\Document as RL_Document;

class Sefs
{
    public static function purge()
    {
        if ( ! RL_Document::isClient('administrator'))
        {
            die('No Access!');
        }

        // need to set the user agent, to prevent breaking when debugging is switched on
        $_SERVER['HTTP_USER_AGENT'] = '';

        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->delete('#__betterpreview_sefs');
        $db->setQuery($query);
        $db->execute();

        die();
    }
}
