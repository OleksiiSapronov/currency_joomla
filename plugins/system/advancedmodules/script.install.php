<?php
/**
 * @package         Advanced Module Manager
 * @version         9.9.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;

class PlgSystemAdvancedModulesInstallerScript
{
    public function postflight($install_type, $adapter)
    {
        if ( ! in_array($install_type, ['install', 'update']))
        {
            return true;
        }

        self::setPluginOrdering();

        return true;
    }

    private static function setPluginOrdering()
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->update('#__extensions')
            ->set($db->quoteName('ordering') . ' = -1')
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('advancedmodules'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'));
        $db->setQuery($query);
        $db->execute();

        JFactory::getCache()->clean('_system');
    }
}
