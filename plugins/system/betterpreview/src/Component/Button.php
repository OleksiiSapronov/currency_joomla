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

namespace RegularLabs\Plugin\System\BetterPreview\Component;

defined('_JEXEC') or die;

/**
 ** Plugin that places the button
 */
class Button extends Helper
{
    public function getExtraJavaScript($text)
    {
        return '';
    }
}
