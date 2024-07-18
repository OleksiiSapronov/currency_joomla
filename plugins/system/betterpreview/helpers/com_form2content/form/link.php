<?php
/**
 * Link Helper class: com_form2content.form
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

class helperBetterPreviewLinkForm2ContentForm extends helperBetterPreviewLink
{
	function getLinks()
	{
		$helper = new helperBetterPreviewHelperForm2ContentForm($this->params);

		if (!$item = $helper->getArticle())
		{
			return;
		}

		$parents = $helper->getArticleParents($item);

		return array_merge(array($item), $parents);
	}
}
