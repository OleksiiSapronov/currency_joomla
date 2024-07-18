<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$print = $displayData['print'];
$item = $displayData['item'];
$params = $item->params;
$positions = $params->get('block_position', 0);

$useDefList =
	($params->get('show_modify_date') ||
		$params->get('show_publish_date') ||
		$params->get('show_create_date') ||
		$params->get('show_hits') ||
		$params->get('show_category') ||
		$params->get('show_parent_category') ||
		$params->get('show_author'));
$icons = $params->get('show_print_icon') || $params->get('show_email_icon');

$tplparams = JFactory::getApplication()->getTemplate(true)->params;

$layout = JFactory::getApplication()->input->get('layout');
if ($layout == 'videoplayer'):
	$item->autoplay = JFactory::getApplication()->input->getInt('autoplay', 0);
	echo JLayoutHelper::render('joomla.content.video_play', array('item' => $item, 'context' => 'iframe'));
else:
	?>
<article class="article" itemscope itemtype="http://schema.org/Article">
	<meta itemprop="inLanguage" content="<?php echo ($item->language === '*') ? JFactory::getConfig()->get('language') : $item->language; ?>" />
	<meta itemprop="url" content="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid)) ?>" />
	<?php if ($params->get('show_title')) : ?>
		<?php echo JLayoutHelper::render('joomla.content.item_title', array('item' => $item, 'params' => $params, 'title-tag'=>'h1')); ?>
	<?php endif; ?>
	<?php if ($icons || $print || ($useDefList && in_array($positions, array(0, 2)))) : ?>
		<aside class="article-aside article-aside-full">
			<?php if ($useDefList && in_array($positions, array(0, 2))) : ?>
			<?php echo JLayoutHelper::render('joomla.content.info_block.magazine_block', array('item' => $item, 'params' => $params, 'position' => 'above')); ?>
			<?php endif ?>

			<?php if ($print): ?>
				<div id="pop-print" class="hidden-print">
					<?php echo JHtml::_('icon.print_screen', $item, $params); ?>
				</div>
			<?php endif ?>
      
      <?php if ($icons && !$print): ?>
        <?php echo JLayoutHelper::render('joomla.content.magazine_icons', array('item' => $item, 'params' => $params)); ?>
			<?php endif; ?>
		</aside>
	<?php endif; ?>

	<section class="article-intro-media">
		<section class="video-wrap">
			<div id="ja-main-player" class="main-player" itemprop="video">
				<?php echo JLayoutHelper::render('joomla.content.video_play', array('item' => $item, 'context' => 'featured')); ?>
			</div>
		</section>
	</section>

	<section class="row article-navigation top">
		<?php if (isset ($item->pagination)) echo $item->pagination ?>
	</section>

	<section class="article-full">

		<div class="article-content-main">

		<?php if (!$params->get('show_intro', 1)) : ?>
			<?php echo $item->event->afterDisplayTitle; ?>
		<?php endif; ?>
		<?php echo $item->event->beforeDisplayContent; ?>

		<section class="article-content" itemprop="articleBody">
			<?php echo JLayoutHelper::render('joomla.content.info_block.topic', array('item' => $item)); ?>
			<?php echo $item->text; ?>
			
			<?php if ($params->get('show_tags', 1) && !empty($item->tags)) : ?>
				<?php echo JLayoutHelper::render('joomla.content.tags', $item->tags->itemTags); ?>
			<?php endif; ?>
		</section>

		<?php if ($useDefList && in_array($positions, array(1, 2))) : ?>
			<footer class="article-footer">
				<?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $item, 'params' => $params, 'position' => 'below')); ?>
			</footer>
		<?php endif; ?>

		<?php echo $item->event->afterDisplayContent; ?>

		</div>
	</section>

	<section class="row article-navigation bottom">
		<?php if (isset ($item->pagination)) echo $item->pagination ?>
	</section>

</article>
<?php endif; ?>