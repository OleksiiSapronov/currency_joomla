<?php
/**
 * @package         ReReplacer
 * @version         13.2.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Object\CMSObject as JObject;

/**
 * Component Helper
 */
class ReReplacerHelper
{
    public static $extension = 'com_rereplacer';

    /**
     * Configure the Itembar.
     *
     * @param string    The name of the active view.
     */
    public static function addSubmenu($vName)
    {
        // No submenu for this component.
    }

    /**
     * Gets a list of the actions that can be performed.
     *
     * @return    JObject
     */
    public static function getActions()
    {
        $user      = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
        $result    = new JObject;
        $assetName = 'com_rereplacer';

        $actions = [
            'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete',
        ];

        foreach ($actions as $action)
        {
            $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;
    }

    /**
     * Determines if the plugin for ReReplacer to work is enabled.
     *
     * @return    boolean
     */
    public static function isEnabled()
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select($db->quote('enabled'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('rereplacer'));

        $db->setQuery($query);

        return (boolean) $db->loadResult();
    }
}
