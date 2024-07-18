<?php
/**
 * @package         Regular Labs Extension Manager
 * @version         8.5.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed as JAccessExceptionNotallowed;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Installer\Installer as JInstaller;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\MVC\Controller\BaseController as JController;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Language as RL_Language;

// return if Regular Labs Library plugin is not installed
if (
    ! is_file(JPATH_PLUGINS . '/system/regularlabs/regularlabs.xml')
    || ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php')
)
{
    $msg = JText::_('RLEM_REGULAR_LABS_LIBRARY_NOT_INSTALLED')
        . ' ' . JText::sprintf('RLEM_EXTENSION_CAN_NOT_FUNCTION', JText::_('COM_REGULARLABSMANAGER'));
    JFactory::getApplication()->enqueueMessage($msg, 'error');

    return;
}

$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

// Access check.
if ( ! $user->authorise('core.manage', 'com_regularlabsmanager'))
{
    throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3, 'COM_REGULARLABSMANAGER'))
{
    return;
}

$helper = new RegularLabsManagerHelper;

if ( ! $helper->isFrameworkEnabled())
{
    return false;
}

RL_Language::load('plg_system_regularlabs');
RL_Language::load('com_modules', JPATH_ADMINISTRATOR);

$helper->uninstallNoNumberExtensionManager();

JController::getInstance('RegularLabsManager')
    ->execute(JFactory::getApplication()->input->get('task'))
    ->redirect();

class RegularLabsManagerHelper
{
    private string $_lang_prefix = 'RLEM';
    private string $_title       = 'COM_REGULARLABSMANAGER';

    /**
     * Check if the Regular Labs Library is enabled
     *
     * @return bool
     */
    public function isFrameworkEnabled()
    {
        // Return false if Regular Labs Library is not installed
        if ( ! $this->isFrameworkInstalled())
        {
            return false;
        }

        if ( ! JPluginHelper::isEnabled('system', 'regularlabs'))
        {
            $this->throwError(
                JText::_($this->_lang_prefix . '_REGULAR_LABS_LIBRARY_NOT_ENABLED')
                . ' ' . JText::sprintf($this->_lang_prefix . '_EXTENSION_CAN_NOT_FUNCTION', JText::_($this->_title))
            );

            return false;
        }

        return true;
    }

    /**
     * Check if the Regular Labs Library is installed
     *
     * @return bool
     */
    public function isFrameworkInstalled()
    {
        jimport('joomla.filesystem.file');

        if (
            ! is_file(JPATH_PLUGINS . '/system/regularlabs/regularlabs.xml')
            || ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php')
        )
        {
            $this->throwError(
                JText::_($this->_lang_prefix . '_REGULAR_LABS_LIBRARY_NOT_INSTALLED')
                . ' ' . JText::sprintf($this->_lang_prefix . '_EXTENSION_CAN_NOT_FUNCTION', JText::_($this->_title))
            );

            return false;
        }

        return true;
    }

    /**
     * Place an error in the message queue
     */
    public function throwError($text)
    {
        JFactory::getApplication()->enqueueMessage($text, 'error');
    }

    public function uninstallNoNumberExtensionManager()
    {
        jimport('joomla.filesystem.folder');

        // Check if old NoNumber Extension Manager is still installed
        if ( ! JFolder::exists(JPATH_ADMINISTRATOR . '/components/com_nonumbermanager'))
        {
            return;
        }

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('extension_id')
            ->from('#__extensions')
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_nonumbermanager'));

        $db->setQuery($query);
        $id = $db->loadResult();

        if (empty($id))
        {
            return;
        }

        $installer = new JInstaller;
        $installer->uninstall('component', $id);
    }
}
