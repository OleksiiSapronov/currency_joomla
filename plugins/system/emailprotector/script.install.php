<?php
/**
 * @package         Email Protector
 * @version         5.2.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;

class PlgSystemEmailProtectorInstallerScript
{
    public function postflight($install_type, $adapter)
    {
        if ( ! in_array($install_type, ['install', 'update']))
        {
            return true;
        }

        self::disableCoreEmailCloaker();

        return true;
    }

    private static function disableCoreEmailCloaker()
    {
        $db = JFactory::getDbo();

        // Disable the core Email Cloaking plugin
        $query = $db->getQuery(true)
            ->update('#__extensions as e')
            ->set('e.enabled = 0')
            ->where('e.name = ' . $db->quote('plg_content_emailcloak'));
        $db->setQuery($query);
        $db->execute();

        JFactory::getCache()->clean('_system');
    }
}
