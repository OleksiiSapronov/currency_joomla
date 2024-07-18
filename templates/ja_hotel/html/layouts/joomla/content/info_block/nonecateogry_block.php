<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$canEdit = $displayData['item']->params->get('access-edit');
$blockPosition = $displayData['params']->get('info_block_position', 0);
$item = $displayData['item'];
$title = $this->escape($item->category_title);
if (!isset($item->catslug)) {
	$item->catslug = $item->category_alias ? ($item->catid.':'.$item->category_alias) : $item->catid;
}

$author = ($item->created_by_alias ? $item->created_by_alias : $item->author);
$author = '<span itemprop="name">' . $author . '</span>';
$authorobj = JUser::getInstance($item->created_by);
$ahtorparams = new JRegistry;
$ahtorparams->loadString ($authorobj->params);
$avatar = $ahtorparams->get ('avatar');
?>
	<dl class="article-info muted">

		<?php if (($displayData['position'] == 'above' && ($blockPosition == 0 || $blockPosition == 2))
				|| ($displayData['position'] == 'below' && ($blockPosition == 1))
				) : ?>

			<dt class="article-info-term">
				<?php // TODO: implement info_block_show_title param to hide article info title ?>
				<?php if ($displayData['params']->get('info_block_show_title', 1)) : ?>
					<?php echo JText::_('COM_CONTENT_ARTICLE_INFO'); ?>
				<?php endif; ?>
			</dt>
      <dd class="hidden"></dd>

      <?php if ($displayData['params']->get('show_author')) : ?>
			<dd class="createdby hasTooltip" itemprop="author" title="<?php echo JText::sprintf('COM_CONTENT_WRITTEN_BY', ''); ?>">
				<i class="fa fa-user"></i>
				<?php if (!empty($displayData['item']->contact_link ) && $displayData['params']->get('link_author') == true) : ?>
					<?php echo JHtml::_('link', $displayData['item']->contact_link, $author, array('itemprop' => 'url')); ?>
				<?php else :?>
					<?php echo $author; ?>
				<?php endif; ?>
			</dd>
      <?php endif; ?>

			<?php if ($displayData['params']->get('show_parent_category') && !empty($displayData['item']->parent_slug)) : ?>
				<?php echo JLayoutHelper::render('joomla.content.info_block.parent_category', $displayData); ?>
			<?php endif; ?>

			<?php if ($displayData['params']->get('show_publish_date')) : ?>
				<?php echo JLayoutHelper::render('joomla.content.info_block.publish_date', $displayData); ?>
			<?php endif; ?>

			<?php if ($displayData['params']->get('show_date')) : ?>
				<?php echo JLayoutHelper::render('joomla.content.info_block.date', $displayData); ?>
			<?php endif; ?>

			<?php if ($canEdit) : ?>
				<?php echo JLayoutHelper::render('joomla.content.info_block.item_edit', $displayData); ?>
			<?php endif; ?>
		<?php endif; ?>

		<?php if (($displayData['position'] == 'above' && ($blockPosition == 0))
				|| ($displayData['position'] == 'below' && ($blockPosition == 1 || $blockPosition == 2))
				) : ?>
      <dt class="hidden"></dt>
      <dd class="hidden"></dd>
			<?php if ($displayData['params']->get('show_create_date')) : ?>
				<?php echo JLayoutHelper::render('joomla.content.info_block.create_date', $displayData); ?>
			<?php endif; ?>

			<?php if ($displayData['params']->get('show_modify_date')) : ?>
				<?php echo JLayoutHelper::render('joomla.content.info_block.modify_date', $displayData); ?>
			<?php endif; ?>

			<?php if ($displayData['params']->get('show_hits')) : ?>
				<?php echo JLayoutHelper::render('joomla.content.info_block.hits', $displayData); ?>
			<?php endif; ?>
		<?php endif; ?>
	</dl>
