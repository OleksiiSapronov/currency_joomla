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

use Joomla\CMS\Access\Exception\NotAllowed as JAccessExceptionNotallowed;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\MVC\Controller\BaseController as JController;
use Joomla\CMS\Plugin\PluginHelper as JPluginHelper;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Language as RL_Language;

$app  = JFactory::getApplication();
$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

$allow_edit        = $user->authorise('module.edit.frontend', 'com_modules');
$allow_edit_module = $app->input->get('id') ? $user->authorise('module.edit.frontend', 'com_modules.module.' . $app->input->get('id')) : true;

if ( ! $allow_edit && ! $allow_edit_module)
{
    throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

JFactory::getLanguage()->load('com_modules', JPATH_ADMINISTRATOR);
JFactory::getLanguage()->load('com_advancedmodules', JPATH_ADMINISTRATOR . '/components/com_advancedmodules');

jimport('joomla.filesystem.file');

// return if Regular Labs Library plugin is not installed
if (
    ! is_file(JPATH_PLUGINS . '/system/regularlabs/regularlabs.xml')
    || ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php')
)
{
    $msg = JText::_('AMM_REGULAR_LABS_LIBRARY_NOT_INSTALLED')
        . ' ' . JText::sprintf('AMM_EXTENSION_CAN_NOT_FUNCTION', JText::_('COM_ADVANCEDMODULES'));
    JFactory::getApplication()->enqueueMessage($msg, 'error');

    return;
}

// give notice if Regular Labs Library plugin is not enabled
if ( ! JPluginHelper::isEnabled('system', 'regularlabs'))
{
    $msg = JText::_('AMM_REGULAR_LABS_LIBRARY_NOT_ENABLED')
        . ' ' . JText::sprintf('AMM_EXTENSION_CAN_NOT_FUNCTION', JText::_('COM_ADVANCEDMODULES'));
    JFactory::getApplication()->enqueueMessage($msg, 'notice');
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3, 'COM_ADVANCEDMODULES'))
{
    return;
}

RL_Language::load('plg_system_regularlabs');
// Load admin main core language strings
RL_Language::load('', JPATH_ADMINISTRATOR);

// Tell the browser not to cache this page.
$app->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true);

$config = [];
if ($app->input->get('task') === 'module.orderPosition')
{
    $config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
}

$controller = JController::getInstance('AdvancedModules', $config);
$controller->execute($app->input->get('task'));
$controller->redirect();
