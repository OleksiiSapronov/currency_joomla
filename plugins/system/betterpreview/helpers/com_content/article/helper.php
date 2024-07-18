<?php
/**
 * Helper class: com_content.article
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

include_once JPATH_SITE . '/components/com_content/helpers/route.php';

class helperBetterPreviewHelperContentArticle extends plgSystemBetterPreviewHelper
{
	function getArticle()
	{
		if (JFactory::getApplication()->input->get('layout', 'edit') != 'edit'
			|| !JFactory::getApplication()->input->get('id')
		)
		{
			return;
		}

		$item = $this->getItem(
			JFactory::getApplication()->input->get('id'),
			'content',
			array('name' => 'title', 'published' => 'state', 'language' => 'language', 'parent' => 'catid'),
			array('type' => 'NN_ARTICLE')
		);

		$item->url = ContentHelperRoute::getArticleRoute($item->id, $item->parent, $item->language);

		return $item;
	}

	function getArticleParents($item)
	{
		if (empty($item)
			|| JFactory::getApplication()->input->get('layout', 'edit') != 'edit'
			|| !JFactory::getApplication()->input->get('id')
		)
		{
			return false;
		}

		$parents = $this->getParents(
			$item,
			'categories',
			array('name' => 'title', 'parent' => 'parent_id', 'language' => 'language'),
			array('type' => 'JCATEGORY'),
			1
		);

		foreach ($parents as &$parent)
		{
			$parent->url = ContentHelperRoute::getCategoryRoute($parent->id, $item->language);
		}

		return $parents;
	}
}
