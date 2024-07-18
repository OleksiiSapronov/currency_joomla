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

defined('_JEXEC') or die;

use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\EditorButtonPlugin as RL_EditorButtonPlugin;
use RegularLabs\Library\Extension as RL_Extension;
use RegularLabs\Plugin\System\BetterPreview\Component as BP_Component;

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php')
    || ! is_file(JPATH_LIBRARIES . '/regularlabs/src/EditorButtonPlugin.php')
)
{
    return;
}

if ( ! is_file(JPATH_PLUGINS . '/system/betterpreview/vendor/autoload.php'))
{
    return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3))
{
    RL_Extension::disable('betterpreview', 'plugin', 'editors-xtd');

    return;
}

require_once JPATH_PLUGINS . '/system/betterpreview/vendor/autoload.php';

if (true)
{
    class PlgButtonBetterPreview extends RL_EditorButtonPlugin
    {
        var $require_core_auth = false;

        public function extraChecks($params)
        {
            if (RL_Document::isClient('site'))
            {
                return false;
            }

            if ( ! $class = BP_Component::getClass('Button'))
            {
                return false;
            }

            return true;
        }
    }
}
