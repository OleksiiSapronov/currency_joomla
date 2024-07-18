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

namespace RegularLabs\Plugin\System\BetterPreview\Component\K2\Itemlist;

defined('_JEXEC') or die;

use RegularLabs\Plugin\System\BetterPreview\Component\Preview as Main_Preview;

class Preview extends Main_Preview
{
    public function getShowIntro(&$article)
    {
        if ( ! isset($article->params))
        {
            return 1;
        }

        if ( ! is_object($article->params))
        {
            $params = (object) json_decode($article->params);

            return $params->catItemIntroText;
        }

        return $article->params->get('catItemIntroText', '1');
    }

    public function renderPreview(&$article, $context)
    {
        if ($context != 'com_k2.category' || ! isset($article->description))
        {
            return;
        }

        parent::render($article, $context);
    }

    public function states()
    {
        parent::initStates(
            'k2_categories',
            [],
            'k2_categories',
            []
        );
    }
}
