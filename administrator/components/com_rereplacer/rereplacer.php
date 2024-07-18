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

use Joomla\CMS\Access\Exception\NotAllowed as JAccessExceptionNotallowed;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\MVC\Controller\BaseController as JController;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Language as RL_Language;

$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

// Access check.
if ( ! $user->authorise('core.manage', 'com_rereplacer'))
{
    throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

jimport('joomla.filesystem.file');

// return if Regular Labs Library plugin is not installed
if (
    ! is_file(JPATH_PLUGINS . '/system/regularlabs/regularlabs.xml')
    || ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php')
)
{
    $msg = JText::_('RR_REGULAR_LABS_LIBRARY_NOT_INSTALLED')
        . ' ' . JText::sprintf('RR_EXTENSION_CAN_NOT_FUNCTION', JText::_('COM_REREPLACER'));
    JFactory::getApplication()->enqueueMessage($msg, 'error');

    return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3, 'COM_REREPLACER'))
{
    return;
}

RL_Language::load('plg_system_regularlabs');

// give notice if Regular Labs Library plugin is not enabled
if ( ! JPluginHelper::isEnabled('system', 'regularlabs'))
{
    $msg = JText::_('RR_REGULAR_LABS_LIBRARY_NOT_ENABLED')
        . ' ' . JText::sprintf('RR_EXTENSION_CAN_NOT_FUNCTION', JText::_('COM_REREPLACER'));
    JFactory::getApplication()->enqueueMessage($msg, 'notice');
}

$controller = JController::getInstance('ReReplacer');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
