<?php
/**
 * @package         ReReplacer
 * @version         8.8.4PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2020 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\ReReplacer;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\Article as RL_Article;
use RegularLabs\Library\Document as RL_Document;

/**
 * Plugin that replaces stuff
 */
class Helper
{
	public function onContentPrepare($context, &$article)
	{
		$items = Items::getItemList('articles');
		Items::filterItemList($items, $article);

		foreach ($items as $item)
		{
			if ( ! $item->enable_in_category && ! isset($article->catid))
			{
				continue;
			}

			$ignore = [];

			if ( ! $item->enable_in_title)
			{
				$ignore[] = 'title';
			}

			if ( ! $item->enable_in_author)
			{
				$ignore[] = 'created_by_alias';
			}

			RL_Article::process($article, $context, $this, 'replace', [$item, $article], $ignore);
		}
	}

	public function onAfterDispatch()
	{
		// FEED
		if (
			isset(JFactory::getDocument()->items)
			&& (
				RL_Document::isFeed()
				|| JFactory::getApplication()->input->get('option') == 'com_acymailing'
			)
		)
		{
			$context = 'feed';
			$items   = JFactory::getDocument()->items;
			foreach ($items as $item)
			{
				$this->onContentPrepare($context, $item);
			}
		}

		// only in html
		if ( ! RL_Document::isHtml())
		{
			return;
		}

		if ( ! $buffer = RL_Document::getBuffer())
		{
			return;
		}

		if ( ! Tag::tagArea($buffer, 'component'))
		{
			return;
		}

		RL_Document::setBuffer($buffer);
	}

	public function onAfterRender()
	{
		$html = JFactory::getApplication()->getBody();

		if ($html == '')
		{
			return;
		}

		Replace::replaceInAreas($html);

		Clean::cleanLeftoverJunk($html);

		JFactory::getApplication()->setBody($html);
	}

	public function replace(&$string, $item, $article)
	{
		Replace::replace($string, $item, $article);
	}
}
