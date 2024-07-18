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

$aparams = JATemplateHelper::getParams();

$aparams->loadArray($helper->toArray(true));

// Get basic setting
$section_title = $aparams->get('section_title');
$section_info  = $aparams->get('section_info');
$show_section_title = $aparams->get('show_section_title', 0);
$section_link  = $aparams->get('section_link');  

// get news
$catids = $aparams->get('list_categories');
$categories = count($catids) ? JATemplateHelper::loadCategories($catids) : JATemplateHelper::getCategories();

$show_slideshow = (int) $aparams->get('show_slideshow', 1);
// get list articles for each sub cat
JText::script('TPL_LOAD_MODULE_AJAX_DONE');
$display_items = (int) $aparams->get('highlight_count', 4);
$limit = (int) $aparams->get('highlight_limit', 0);
$maxPage = 10;//only get 10 pages by default
if($limit) {
	if($display_items) {
		$maxPage = ceil($display_items / $limit);
	}
	$display_items = $limit;
}
$items = JATemplateHelper::getArticles($aparams, $catids, $display_items);

$cols = $aparams->get('highlight_columns', 2);
$direction = $aparams->get('direction', 'hoz');
$col_width = round(12 / $cols);

$mainwidth = $show_section_title ? 'col-xs-12 col-md-8 col-lg-9' : 'col-xs-12 col-md-12 col-lg-12';

$col_width = $show_section_title ? 'col-xs-12 col-sm-6 col-md-'.$col_width : 'col-xs-12 col-sm-'.$col_width.' col-md-'.$col_width;

$isAjax = ($app->input->get('t3action') == 'module');
$listId = 'magazine-category-module-'.$module->id;

 
?>

<?php if(!$isAjax): ?>
	<?php if($show_slideshow && isset($items[0])): ?>
	<div class="magazine-list gallery-list">
	<!-- SLIDESHOW -->
		<div class=" magazine-categories photo-featured" itemscope itemtype="http://schema.org/ImageObject">
		<?php echo JLayoutHelper::render('joomla.content.gallery_image_detail', array('item' => $items[0])); ?>
		</div>
	</div>
	<?php endif; ?>
	<div class="row equal-height equal-height-child magazine-list gallery-list">
	<!-- MAGAZINE LISTING -->
	<div class="col <?php echo $mainwidth ?> magazine-categories">

	<div class="magazine-category <?php if($limit && count($items) == $limit): ?>  has-loadmore <?php endif; ?>  hidden-xs" id="<?php echo $listId; ?>">
<?php endif; ?>
<?php
$i = 0;
foreach ($items as $item) {
	$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;

	$item->parent_slug = ($item->parent_alias) ? ($item->parent_id . ':' . $item->parent_alias) : $item->parent_id;

	// No link for ROOT category
	if ($item->parent_alias == 'root') {
		$item->parent_slug = null;
	}

	$item->catslug = $item->category_alias ? ($item->catid . ':' . $item->category_alias) : $item->catid;
}
?>

<?php if ($direction == 'hoz'): ?>

	<?php
	$i = 0;
	$t = count($items);  
  $c = $cols;
	$n = ceil($t / $c); 
  $r = 1;
	foreach ($items as $item) : ?>
		<?php if ($i % $cols == 0): /* start new row */ ?>  
			<div class="row row-articles <?php if($r==$n): echo 'last-row'; endif; ?>">  
		<?php $r++; endif ?>
		<div class="<?php echo $col_width ?> magazine-item photos-item">
			<?php echo JATemplateHelper::render($item, 'joomla.content.intro', array('item' => $item, 'params' => $aparams)); ?>
		</div>
		<?php if (++$i % $cols == 0 || $i == $t): /* close row */ ?>
			</div>
		<?php endif ?>
	<?php endforeach; ?>

<?php else: ?>

	<div class="row row-articles">
		<?php
		$t = count($items);
		$c = $cols;
		$n = ceil($t / $c);
		$i = 0;
		?>
		<?php foreach ($items as $item) : ?>
			<?php if ($i == 0): /* start new col */ ?>
				<div class="<?php echo $col_width ?> magazine-item" itemscope itemtype="http://schema.org/ImageObject">
			<?php endif ?>
			<div class="magazine-item-inner">
				<?php echo JATemplateHelper::render($item, 'joomla.content.intro', array('item' => $item, 'params' => $aparams, 'img-size' => 'medium')); ?>
			</div>
			<?php  if (++$i == $n):
				$i = 0;
				$t -= $n;
				$c--;
				$n = $c ? ceil($t / $c) : 0;
				/* close col */
				?>
				</div>
			<?php endif ?>
		<?php endforeach; ?>
	</div>

<?php endif ?>

<?php if(!$isAjax): ?>
	</div>
	<?php if($limit && count($items) == $limit): ?>
		<div class="load-more">
			<button class="btn btn-default btn-info" data-link="<?php echo JUri::getInstance()->toString(); ?>" data-maxpage="<?php echo $maxPage; ?>" onclick="jActions.loadModuleNextPage(this, <?php echo $module->id; ?>, '<?php echo $listId; ?>'); return false;" title="<?php echo JText::_('Load More'); ?>">
				<?php echo JText::_('TPL_LOAD_MORE'); ?>
				<span class="fa fa-spin fa-circle-o-notch" style="display: none"></span>
			</button>
		</div>
	<?php endif; ?>
	</div>
	<!-- //MAGAZINE LISTING -->

	<?php if($show_section_title) : 
    $doc = JFactory::getDocument();
    $sitename = $doc->params->get('sitename');
    if (!$sitename) {
    	$sitename = JFactory::getConfig()->get('sitename');
    }
    $siteUrl  = JURI::base(true);
  ?>
		<div class="col col-xs-12 col-md-4 col-lg-3 magazine-section-heading gallery-section-heading">
      <article>
        <div class="logo-text">
  				<a title="<?php echo $sitename; ?>" href="<?php echo $siteUrl; ?>">
  					<span><?php echo $sitename; ?></span>
  				</a>
  			</div>
  			<h4 class="section-title"><?php echo $section_title; ?></h4>
        <div class="section-info"><?php echo $section_info; ?></div>
        <?php if($section_link): ?><a href="<?php echo $section_link; ?>" class="btn btn-border">View all</a><?php endif; ?>
      </article>
		</div>
	<?php endif; ?>

	</div>
<?php endif; ?>