<?php
/**
 * Link Helper class: com_content.article
 *
 * @package         Better Preview
 * @version         3.4.2
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2015 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

include_once __DIR__ . '/helper.php';

class helperBetterPreviewLinkContentArticle extends helperBetterPreviewLink
{
	function getLinks()
	{
		$helper = new helperBetterPreviewHelperContentArticle($this->params);

		if (!$item = $helper->getArticle())
		{
			return;
		}

		$parents = $helper->getArticleParents($item);

		return array_merge(array($item), $parents);
	}
}
