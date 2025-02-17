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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

if(version_compare(JVERSION, '4', 'ge')){
	class TagsHelperRoute extends \Joomla\Component\Tags\Site\Helper\RouteHelper{};
}
HTMLHelper::addIncludePath(JPATH_COMPONENT.'/helpers');
if(version_compare(JVERSION, '4','lt')){
  HTMLHelper::_('behavior.caption'); 
}
HTMLHelper::_('behavior.core');
HTMLHelper::_('formbehavior.chosen', 'select');

// Get the user object.
$user = Factory::getUser();

// Check if user is allowed to add/edit based on tags permissions.
$canEdit = $user->authorise('core.edit', 'com_tags');
$canCreate = $user->authorise('core.create', 'com_tags');
$canEditState = $user->authorise('core.edit.state', 'com_tags');

$columns = $this->params->get('tag_columns', 1);
// Avoid division by 0 and negative columns.
if ($columns < 1)
{
	$columns = 1;
}
$bsspans = floor(12 / $columns);
if ($bsspans < 1)
{
	$bsspans = 1;
}

$bscolumns = min($columns, floor(12 / $bsspans));
$n = count($this->items);

Factory::getDocument()->addScriptDeclaration("
		var resetFilter = function() {
		document.getElementById('filter-search').value = '';
	}
");
?>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm">
	<?php if ($this->params->get('filter_field') || $this->params->get('show_pagination_limit')) : ?>
	<fieldset class="filters btn-toolbar">
		<?php if ($this->params->get('filter_field')) : ?>
			<div class="btn-group">
				<label class="filter-search-lbl element-invisible" for="filter-search">
					<?php echo Text::_('COM_TAGS_TITLE_FILTER_LABEL') . '&#160;'; ?>
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
			<div class="btn-group pull-right">
				<label for="limit" class="element-invisible">
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

<?php if ($this->items == false || $n === 0) : ?>
	<p><?php echo Text::_('COM_TAGS_NO_TAGS'); ?></p>
<?php else : ?>
	<?php foreach ($this->items as $i => $item) : ?>
		<?php if ($n === 1 || $i === 0 || $bscolumns === 1 || $i % $bscolumns === 0) : ?>
			<ul class="thumbnails">
		<?php endif; ?>
		<?php if ((!empty($item->access)) && in_array($item->access, $this->user->getAuthorisedViewLevels())) : ?>
 			<li class="cat-list-row<?php echo $i % 2; ?>" >
				<h3>
					<a href="<?php echo Route::_(TagsHelperRoute::getTagRoute($item->id . '-' . $item->alias)); ?>">
						<?php echo $this->escape($item->title); ?>
					</a>
				</h3>
		<?php endif; ?>
		<?php if ($this->params->get('all_tags_show_tag_image') && !empty($item->images)) : ?>
			<?php $images  = json_decode($item->images); ?>
			<span class="tag-body">
			<?php if (!empty($images->image_intro)): ?>
				<?php $imgfloat = empty($images->float_intro) ? $this->params->get('float_intro') : $images->float_intro; ?>
				<div class="pull-<?php echo htmlspecialchars($imgfloat); ?> item-image">
					<img
				<?php if ($images->image_intro_caption) : ?>
					<?php echo 'class="caption"' . ' title="' . htmlspecialchars($images->image_intro_caption) . '"'; ?>
				<?php endif; ?>
				src="<?php echo $images->image_intro; ?>" alt="<?php echo htmlspecialchars($images->image_intro_alt); ?>"/>
				</div>
			<?php endif; ?>
			</span>
		<?php endif; ?>
		<div class="caption">
			<?php if ($this->params->get('all_tags_show_tag_description', 1)) : ?>
				<span class="tag-body">
					<?php echo HTMLHelper::_('string.truncate', $item->description, $this->params->get('all_tags_tag_maximum_characters')); ?>
				</span>
			<?php endif; ?>
			<?php if ($this->params->get('all_tags_show_tag_hits')) : ?>
				<span class="list-hits badge badge-info">
					<?php 
					if (version_compare(JVERSION, '3.0', 'ge'))
					{
						 echo Text::sprintf('JGLOBAL_HITS_COUNT', $item->hits);

					}
					else if (version_compare(JVERSION, '2.5', 'ge'))
					{
						echo Text::sprintf('JAGLOBAL_HITS_COUNT', $item->hits);

					}
					else
					{
						echo Text::sprintf('JAGLOBAL_HITS_COUNT', $item->hits);

					}  ?>
				</span>
			<?php endif; ?>
		</div>
	</li>

		<?php if (($i === 0 && $n === 1) || $i === $n - 1 || $bscolumns === 1 || (($i + 1) % $bscolumns === 0)) : ?>
			</ul>
		<?php endif; ?>

	<?php endforeach; ?>
<?php endif;?>

<?php // Add pagination links ?>
<?php if (!empty($this->items)) : ?>
	<?php 
  $pagesTotal = isset($this->pagination->pagesTotal) ? $this->pagination->pagesTotal : $this->pagination->get('pages.total');
	if (($this->params->def('show_pagination', 2) == 1  || ($this->params->get('show_pagination') == 2)) && ($pagesTotal > 1)) : ?>
	<div class="pagination-wrap">

		<?php if ($this->params->def('show_pagination_results', 1)) : ?>
			<p class="counter pull-right">
				<?php echo $this->pagination->getPagesCounter(); ?>
			</p>
		<?php endif; ?>

		<?php echo $this->pagination->getPagesLinks(); ?>
	</div>
	<?php endif; ?>
<?php endif; ?>
</form>
