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

namespace RegularLabs\Plugin\System\BetterPreview\Component\Content\Category;

defined('_JEXEC') or die;

use RegularLabs\Plugin\System\BetterPreview\Component\Preview as Main_Preview;

class Preview extends Main_Preview
{

    public function renderPreview(&$article, $context)
    {
        if ($context != 'com_content.category' || isset($article->introtext))
        {
            return;
        }

        parent::render($article, $context);
    }

    public function states()
    {
        parent::initStates(
            'categories',
            ['parent' => 'parent_id'],
            'categories',
            ['parent' => 'parent_id']
        );
    }
}
