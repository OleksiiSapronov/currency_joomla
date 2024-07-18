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

namespace RegularLabs\Plugin\System\BetterPreview;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;

class PreLoader
{
    public static function _()
    {
        $fid = JFactory::getApplication()->input->get('fid');

        $template = file_get_contents(__DIR__ . '/Layout/PreLoader.html');
        $template = str_replace(
            [
                '{loading}',
                'parent.fid',
            ],
            [
                JText::_('BP_LOADING'),
                'parent.' . $fid,
            ],
            $template
        );

        echo $template;

        die;
    }
}
