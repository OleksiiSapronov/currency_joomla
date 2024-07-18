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

use RegularLabs\Library\ActionLogPlugin as RL_ActionLogPlugin;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Extension as RL_Extension;

defined('_JEXEC') or die;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php')
    || ! is_file(JPATH_LIBRARIES . '/regularlabs/src/ActionLogPlugin.php')
)
{
    return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3))
{
    RL_Extension::disable('advancedmodules', 'plugin', 'actionlog');

    return;
}

if (true)
{
    class PlgActionlogAdvancedModules extends RL_ActionLogPlugin
    {
        public $name  = 'ADVANCEDMODULEMANAGER';
        public $alias = 'advancedmodules';

        public function __construct(&$subject, array $config = [])
        {
            parent::__construct($subject, $config);

            $this->items = [
                'module' => (object) [
                    'title' => 'PLG_ACTIONLOG_JOOMLA_TYPE_MODULE',
                ],
            ];
        }
    }
}
