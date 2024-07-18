<?php
/**
 * @package         Modules Anywhere
 * @version         7.18.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;

class PlgSystemModulesAnywhereInstallerScript
{
    public function postflight($install_type, $adapter)
    {
        if ( ! in_array($install_type, ['install', 'update']))
        {
            return true;
        }

        self::fixOldParams();
        self::disableCoreEditorPlugin();

        return true;
    }

    public function uninstall($adapter)
    {
        self::enableCoreEditorPlugin();
    }

    private static function disableCoreEditorPlugin()
    {
        $db = JFactory::getDbo();

        $query = self::getCoreEditorPluginQuery()
            ->set($db->quoteName('enabled') . ' = 0')
            ->where($db->quoteName('enabled') . ' = 1');
        $db->setQuery($query);
        $db->execute();

        if ( ! $db->getAffectedRows())
        {
            return;
        }

        JFactory::getApplication()->enqueueMessage(JText::_('Joomla\'s own "Module" editor button has been disabled'), 'warning');
    }

    private static function enableCoreEditorPlugin()
    {
        $db = JFactory::getDbo();

        $query = self::getCoreEditorPluginQuery()
            ->set($db->quoteName('enabled') . ' = 1')
            ->where($db->quoteName('enabled') . ' = 0');
        $db->setQuery($query);
        $db->execute();

        if ( ! $db->getAffectedRows())
        {
            return;
        }

        JFactory::getApplication()->enqueueMessage(JText::_('Joomla\'s own "Module" editor button has been re-enabled'), 'warning');
    }

    private static function fixOldParams()
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select($db->quoteName('extension_id'))
            ->select($db->quoteName('params'))
            ->from('#__extensions')
            ->where($db->quoteName('element') . ' = ' . $db->quote('modulesanywhere'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'));
        $db->setQuery($query);

        $plugin = $db->loadObject();

        if (empty($plugin) || empty($plugin->params))
        {
            return;
        }

        $params = json_decode($plugin->params);

        if (empty($params))
        {
            return;
        }

        if (isset($params->handle_core_tags) || ! isset($params->handle_loadposition))
        {
            return;
        }

        $params->handle_core_tags = $params->handle_loadposition;
        unset($params->handle_loadposition);

        $params = json_encode($params);

        $query->clear()
            ->update('#__extensions')
            ->set($db->quoteName('params') . ' = ' . $db->quote($params))
            ->where($db->quoteName('extension_id') . ' = ' . $db->quote($plugin->extension_id));
        $db->setQuery($query);
        $db->execute();
    }

    private static function getCoreEditorPluginQuery()
    {
        $db = JFactory::getDbo();

        return $db->getQuery(true)
            ->update('#__extensions')
            ->where($db->quoteName('element') . ' = ' . $db->quote('module'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('editors-xtd'))
            ->where($db->quoteName('custom_data') . ' NOT LIKE ' . $db->quote('%modulesanywhere_ignore%'));
    }
}
