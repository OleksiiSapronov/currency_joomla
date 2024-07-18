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

use Joomla\CMS\Object\CMSObject as JObject;
use RegularLabs\Library\EditorButtonHelper as RL_EditorButtonHelper;

/**
 ** Plugin that places the button
 */
class PlgButtonModulesAnywhereHelper extends RL_EditorButtonHelper
{
    /**
     * Display the button
     *
     * @param string $editor_name
     *
     * @return JObject|null A button object
     */
    public function render($editor_name)
    {
        return $this->renderPopupButton($editor_name);
    }
}
