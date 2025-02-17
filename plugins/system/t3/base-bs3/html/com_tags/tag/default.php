<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;
// Note that there are certain parts of this layout used only when there is exactly one tag.

HTMLHelper::addIncludePath(JPATH_COMPONENT.'/helpers');
$isSingleTag = count($this->item) === 1;
$htag        = $this->params->get('show_page_heading') ? 'h2' : 'h1';
?>
<div class="com-tags-tag tag-category<?php echo $this->pageclass_sfx; ?>">
<?php  if ($this->params->get('show_page_heading')) : ?>
<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php endif;  ?>

<?php if($this->params->get('show_tag_title', 1)) : ?>
	<<?php echo $htag; ?>>
		<?php echo HTMLHelper::_('content.prepare', $this->tags_title, '', 'com_tag.tag'); ?>
	</<?php echo $htag; ?>>
<?php endif; ?>

<?php // We only show a tag description if there is a single tag. ?>
<?php  if (count($this->item) === 1 && (($this->params->get('tag_list_show_tag_image', 1)) || $this->params->get('tag_list_show_tag_description', 1))) : ?>
	<div class="category-desc">
	<?php $images = json_decode($this->item[0]->images); ?>
	<?php if (count($this->item) === 1 && ($this->params->get('tag_list_show_tag_image', 1) || $this->params->get('tag_list_show_tag_description', 1))) : ?>
		<img src="<?php echo htmlspecialchars($images->image_fulltext??'', ENT_QUOTES, 'UTF-8'); ?>"
					alt="<?php echo htmlspecialchars($images->image_fulltext_alt??'', ENT_QUOTES, 'UTF-8'); ?>">
	<?php endif; ?>
	<?php if ($this->params->get('tag_list_show_tag_description') == 1 && $this->item[0]->description) : ?>
		<?php echo HTMLHelper::_('content.prepare', $this->item[0]->description, '', 'com_tags.tag'); ?>
	<?php endif; ?>
	<div class="clr"></div>
	</div>
<?php endif; ?>
<?php // If there are multiple tags and a description or image has been supplied use that. ?>
<?php if ($this->params->get('tag_list_show_tag_description', 1) || $this->params->get('tag_list_show_tag_image', 1)): ?>
		<?php if ($this->params->get('tag_list_show_tag_image', 1) == 1 && $this->params->get('tag_list_image')) :?>
			<img src="<?php echo $this->params->get('tag_list_image');?>" />
		<?php endif; ?>
		<?php if ($this->params->get('tag_list_show_tag_description', 1) && $this->params->get('tag_list_description', '') > '') :?>
			<?php echo HTMLHelper::_('content.prepare', $this->params->get('tag_list_description'), '', 'com_tags.tag'); ?>
		<?php endif; ?>

<?php endif; ?>

<?php echo $this->loadTemplate('items'); ?>
	<?php 
  $pagesTotal = isset($this->pagination->pagesTotal) ? $this->pagination->pagesTotal : $this->pagination->get('pages.total');
  if (($this->params->def('show_pagination', 1) == 1  || ($this->params->get('show_pagination') == 2)) && ($pagesTotal > 1)) : ?>
	<div class="pagination-wrap">
		<?php  if ($this->params->def('show_pagination_results', 1)) : ?>
		<p class="counter pull-right"> <?php echo $this->pagination->getPagesCounter(); ?> </p>
		<?php endif; ?>
		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
	<?php  endif; ?>

</div>
