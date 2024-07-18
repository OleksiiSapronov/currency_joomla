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

namespace RegularLabs\Plugin\System\BetterPreview\Component\Form2content\Form;

defined('_JEXEC') or die;

use RegularLabs\Plugin\System\BetterPreview\Component\Button as Main_Button;
use RegularLabs\Plugin\System\BetterPreview\Component\Menu;

class Button extends Main_Button
{
    public function getExtraJavaScript($text)
    {
        return '
                cat = document.getElementById("jform_catid");
                category_title = cat == undefined ? "" : cat.options[cat.selectedIndex].text.replace(/^(\s*-\s+)*/, "").trim();
                overrides = {
                        category_title: category_title,
                    };
            ';
    }

    public function getURL($name)
    {
        if ( ! $item = Helper::getArticle())
        {
            return false;
        }

        Menu::setItemId($item);

        return $item->url;
    }
}
