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

use RegularLabs\Plugin\System\BetterPreview\Component\Link as Main_Link;

class Link extends Main_Link
{
    public function getLinks()
    {
        if ( ! $item = Helper::getArticle())
        {
            return [];
        }

        $parents = Helper::getArticleParents($item);

        return array_merge([$item], $parents);
    }
}
