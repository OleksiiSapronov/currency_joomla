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

namespace RegularLabs\Plugin\System\AdvancedModules;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use PlgSystemAdvancedModuleHelper;

class ModuleHelper
{
    public static function registerEvents()
    {
        require_once __DIR__ . '/Helpers/advancedmodulehelper.php';
        $class = new PlgSystemAdvancedModuleHelper;

        JFactory::getApplication()->registerEvent('onRenderModule', [$class, 'onRenderModule']);
        JFactory::getApplication()->registerEvent('onPrepareModuleList', [$class, 'onPrepareModuleList']);
    }
}
