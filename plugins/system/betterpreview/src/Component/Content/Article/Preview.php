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

namespace RegularLabs\Plugin\System\BetterPreview\Component\Content\Article;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Plugin\System\BetterPreview\Component\Preview as Main_Preview;

class Preview extends Main_Preview
{
    public function render(&$article, $context)
    {
        if ($context != 'com_content.article' || ! isset($article->id) || $article->id != JFactory::getApplication()->input->get('id'))
        {
            return;
        }

        parent::render($article, $context);
    }

    public function states()
    {
        parent::initStates(
            'content',
            [
                'published'    => 'state',
                'publish_up'   => 'publish_up',
                'publish_down' => 'publish_down',
                'parent'       => 'catid',
                'hits'         => 'hits',
            ],
            'categories',
            [
                'parent' => 'parent_id',
            ]
        );
    }
}
