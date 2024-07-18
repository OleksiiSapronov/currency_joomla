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

namespace RegularLabs\Plugin\System\BetterPreview\Component\K2\Item;

defined('_JEXEC') or die;

use RegularLabs\Plugin\System\BetterPreview\Component\Button as Main_Button;
use RegularLabs\Plugin\System\BetterPreview\Component\Menu;

class Button extends Main_Button
{
    public function getExtraJavaScript($text)
    {
        return '
                isjform = 0;
                text = text.split(\'<hr id="system-readmore">\');
                introtext = text[0];
                fulltext =  text[1] == undefined ? "" : text[1];
                text = (introtext + " " + fulltext).trim();
                overrides = {
                        text: text,
                        introtext: introtext,
                        fulltext: fulltext,
                    };
            ';
    }

    public function getURL($name)
    {
        if ( ! $item = Helper::getK2Item())
        {
            return false;
        }

        Menu::setItemId($item);

        return $item->url;
    }
}
