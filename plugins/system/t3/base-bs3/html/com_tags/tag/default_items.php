<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
if(version_compare(JVERSION, '4', 'ge')){
}

if(version_compare(JVERSION, '4', 'ge')) {
	//create tag router on Joomla 4
	class TagsHelperRoute extends \Joomla\Component\Tags\Site\Helper\RouteHelper{};

	/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
	$wa = $this->document->getWebAssetManager();
	$wa->useScript('com_tags.tag-default');
}

HTMLHelper::_('behavior.core');


// Get the user object.
$user = Factory::getUser();

// Check if user is allowed to add/edit based on tags permissions.
// Do we really have to make it so people can see unpublished tags???
$canEdit = $user->authorise('core.edit', 'com_tags');
$canCreate = $user->authorise('core.create', 'com_tags');
$canEditState = $user->authorise('core.edit.state', 'com_tags');
$items = $this->items;
$n = count($this->items);

Factory::getDocument()->addScriptDeclaration("
		var resetFilter = function() {
		document.getElementById('filter-search').value = '';
	}
");

?>

<div class="com-tags__items">
	<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm" class="form-inline">
		<?php if ($this->params->get('show_headings') || $this->params->get('filter_field') || $this->params->get('show_pagination_limit')) : ?>
		<fieldset class="filters btn-toolbar">
			<?php if ($this->params->get('filter_field')) :?>
				<div class="com-tags-tags__filter btn-group">
					<label class="filter-search-lbl element-invisible visually-hidden" for="filter-search">
						<?php echo Text::_('COM_TAGS_TITLE_FILTER_LABEL').'&#160;'; ?>
					</label>
					<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('list.filter')); ?>" class="inputbox" onchange="document.adminForm.submit();" title="<?php echo Text::_('COM_TAGS_FILTER_SEARCH_DESC'); ?>" placeholder="<?php echo Text::_('COM_TAGS_TITLE_FILTER_LABEL'); ?>" />
					<button type="button" name="filter-search-button" title="<?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?>" onclick="document.adminForm.submit();" class="btn">
						<span class="fa fa-search"></span>
					</button>
					<button type="reset" name="filter-clear-button" title="<?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>" class="btn" onclick="resetFilter(); document.adminForm.submit();">
						<span class="fa fa-remove"></span>
					</button>			
				</div>
			<?php endif; ?>
			<?php if ($this->params->get('show_pagination_limit')) : ?>
				<div class="btn-group pull-right float-right float-end">
					<label for="limit" class="element-invisible visually-hidden">
						<?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?>
					</label>
					<?php echo $this->pagination->getLimitBox(); ?>
				</div>
			<?php endif; ?>

			<input type="hidden" name="filter_order" value="" />
			<input type="hidden" name="filter_order_Dir" value="" />
			<input type="hidden" name="limitstart" value="" />
			<input type="hidden" name="task" value="" />
			<div class="clearfix"></div>
		</fieldset>
		<?php endif; ?>

		<?php if ($this->items === false || $n === 0) : ?>
			<p> <?php echo Text::_('COM_TAGS_NO_ITEMS'); ?></p>
		<?php else : ?>

		<ul class="category list-striped list-unstyled" itemscope itemtype="http://schema.org/ItemList">
			<?php foreach ($items as $i => $item) : ?>
				<?php if ($item->core_state == 0) : ?>
					<li class="system-unpublished cat-list-row<?php echo $i % 2; ?>" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
				<?php else: ?>
					<li class="cat-list-row<?php echo $i % 2; ?> clearfix" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
					<?php if (($item->type_alias === 'com_users.category') || ($item->type_alias === 'com_banners.category')) : ?>
						<h3 class="item-tag-title" itemprop="name"><?php echo $this->escape($item->core_title); ?></h3>
					<?php else : ?>
					<h3 class="item-tag-title" itemprop="name">
						<a href="<?php echo Route::_($item->link); ?>" itemprop="url">
							<?php echo $this->escape($item->core_title); ?>
						</a>
					</h3>
					<?php endif; ?>
				<?php endif; ?>
				<?php // Content is generated by content plugin event "onContentAfterTitle" ?>
				<?php echo $item->event->afterDisplayTitle; ?>
				<?php $images  = json_decode($item->core_images);?>
				<?php if ($this->params->get('tag_list_show_item_image', 1) == 1 && !empty($images->image_intro)) :?>
					<a href="<?php echo Route::_(TagsHelperRoute::getItemRoute($item->content_item_id, $item->core_alias, $item->core_catid, $item->core_language, $item->type_alias, $item->router)); ?>" itemprop="url" class="item-tag-image">
					<img src="<?php echo htmlspecialchars($images->image_intro);?>" alt="<?php echo htmlspecialchars($images->image_intro_alt); ?>" itemprop="image">
					</a>
				<?php endif; ?>
				<?php if ($this->params->get('tag_list_show_item_description', 1)) : ?>
					<?php // Content is generated by content plugin event "onContentBeforeDisplay" ?>
					<?php echo $item->event->beforeDisplayContent; ?>
					<span class="tag-body" itemprop="description">
						<?php echo HTMLHelper::_('string.truncate', $item->core_body, $this->params->get('tag_list_item_maximum_characters')); ?>
					</span>
					<?php // Content is generated by content plugin event "onContentAfterDisplay" ?>
					<?php echo $item->event->afterDisplayContent; ?>
				<?php endif; ?>
					</li>
			<?php endforeach; ?>
		</ul>

	<?php endif; ?>
	</form>
</div>