<?php
/**
 * ------------------------------------------------------------------------
 * JA Hotel Template
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2011 J.O.O.M Solutions Co., Ltd. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: J.O.O.M Solutions Co., Ltd
 * Websites:  http://www.joomlart.com -  http://www.joomlancers.com
 * This file may not be redistributed in whole or significant part.
 * ------------------------------------------------------------------------
*/

defined('_JEXEC') or die;
// Note that there are certain parts of this layout used only when there is exactly one tag.

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
$isSingleTag = (count($this->item) == 1);
?>
<div class="tag-category<?php echo $this->pageclass_sfx; ?>">
	<?php  if ($this->params->get('show_page_heading') || ($this->params->get('tag_list_show_tag_description', 0) || $this->params->get('show_description_image', 0)) || (count($this->item) == 1 && (($this->params->get('tag_list_show_tag_image', 0)) || $this->params->get('tag_list_show_tag_description', 0)))) : ?>
	<div class="tags-box">
		<?php  if ($this->params->get('show_page_heading')) : ?>
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
		<?php endif;  ?>
		<?php if($this->params->get('show_tag_title', 1)) : ?>
		<h2>
			<?php echo JHtml::_('content.prepare', $this->tags_title, '', 'com_tag.tag'); ?>
		</h2>
		<?php endif; ?>

		<?php // We only show a tag description if there is a single tag. ?>
		<?php  if (count($this->item) == 1 && (($this->params->get('tag_list_show_tag_image', 0)) || $this->params->get('tag_list_show_tag_description', 0))) : ?>
		<div class="tag-info clearfix">
			<div class="category-desc">
			<?php $images = json_decode($this->item[0]->images); ?>
			<?php if ($this->params->get('tag_list_show_tag_image', 1) == 1 && !empty($images->image_fulltext)) : ?>
				<img src="<?php echo htmlspecialchars($images->image_fulltext);?>" alt="<?php echo htmlspecialchars($images->image_fulltext_alt); ?>" />
			<?php endif; ?>
			<?php if ($this->params->get('tag_list_show_tag_description') == 1 && $this->item[0]->description) : ?>
				<?php echo JHtml::_('content.prepare', $this->item[0]->description, '', 'com_tags.tag'); ?>
			<?php endif; ?>
			<div class="clr"></div>
			</div>
		</div>
		<?php endif; ?>

		<?php // If there are multiple tags and a description or image has been supplied use that. ?>
		<?php if ($this->params->get('tag_list_show_tag_description', 0) || $this->params->get('show_description_image', 0)): ?>
		<div class="tag-info clearfix">
				<?php if ($this->params->get('show_description_image', 1) == 1 && $this->params->get('tag_list_image')) :?>
					<img src="<?php echo $this->params->get('tag_list_image');?>">
				<?php endif; ?>
				<?php if ($this->params->get('tag_list_description', '') > '') :?>
					<?php echo JHtml::_('content.prepare', $this->params->get('tag_list_description'), '', 'com_tags.tag'); ?>
				<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<?php echo $this->loadTemplate('items'); ?>

	<?php
  $pagesTotal = isset($this->pagination->pagesTotal) ? $this->pagination->pagesTotal : $this->pagination->get('pages.total');
  if (($this->params->def('show_pagination', 1) == 1  || ($this->params->get('show_pagination') == 2)) && ($pagesTotal > 1)) : ?>
	<div class="pagination-wrap clearfix">
		<?php  if ($this->params->def('show_pagination_results', 1)) : ?>
		<p class="counter pull-right"> <?php echo $this->pagination->getPagesCounter(); ?> </p>
		<?php endif; ?>
		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
	<?php  endif; ?>

</div>
