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

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers');
JHtml::addIncludePath(T3_PATH.'/html/com_content');
JHtml::addIncludePath(dirname(dirname(__FILE__)));
JHtml::_('behavior.caption');


$menu = JFactory::getApplication()->getMenu();
$active = $menu->getActive() ? $menu->getActive() : $menu->getDefault();
$params = $this->params;      
$grid_lg = $params->get('itemlg',4);
$grid_md = $params->get('itemmd',3);
$grid_sm = $params->get('itemsm',2);
$grid_smx = $params->get('itemsmx',2);
$grid_xs = $params->get('itemxs',1);
$gutter  = $params->get('gutter',30);
$mgutter = $gutter / 2;

$grid_cls = "grid-xs-{$grid_xs} grid-smx-{$grid_smx} grid-sm-{$grid_sm} grid-md-{$grid_md} grid-lg-{$grid_lg}";
$grid_css = ".ja-masonry-wrap {margin-left:-{$mgutter}px;margin-right:-{$mgutter}px;}\n";
$grid_css .= ".ja-masonry-wrap .item{padding-left:{$mgutter}px;padding-right:{$mgutter}px;padding-bottom:{$gutter}px}\n";
$grid_css .= ".ja-masonry-wrap .page-header {padding-left:{$mgutter}px;padding-right:{$mgutter}px;}\n";

$dir = JFactory::getDocument();

if ($dir->direction=='ltr'):

$grid_css .= ".ja-masonry-wrap .categories-list {margin-right:{$mgutter}px;}\n";

else:

$grid_css .= ".ja-masonry-wrap .categories-list {margin-left:{$mgutter}px;}\n";

endif;

$doc = JFactory::getDocument();
$doc->addStyleSheet (T3_TEMPLATE_URL . '/css/masonry.css');
$doc->addStyleDeclaration ($grid_css);
$doc->addScript (T3_TEMPLATE_URL . '/js/jquery.infinitescroll.min.js');
$doc->addScript (T3_TEMPLATE_URL . '/js/imagesloaded.pkgd.min.js');
$doc->addScript (T3_TEMPLATE_URL . '/js/isotope.pkgd.min.js');
?>

<div class="ja-masonry-wrap">
 <?php if ($this->params->get('show_page_heading', 1)) : ?>
  <div class="page-header clearfix">
    <h1 class="page-title"> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
  </div>
  <?php endif; ?>
	
	<?php if ($this->params->get('show_description', 1) || $this->params->def('show_description_image', 1) || $this->params->get('show_category_title', 1) or $this->params->get('page_subheading')) : ?>
	<div class="category-desc clearfix">
		
		<?php if ($this->params->get('show_description_image') && $this->category->getParams()->get('image')) : ?>
			<img src="<?php echo $this->category->getParams()->get('image'); ?>"/>
		<?php endif; ?>
		
		<?php if ($this->params->get('show_category_title', 1) or $this->params->get('page_subheading')) : ?>
			<div class="page-subheader clearfix">
				<h2 class="page-subtitle"><?php echo $this->escape($this->params->get('page_subheading')); ?>
					<?php if ($this->params->get('show_category_title')) : ?>
					<small class="subheading-category"><?php echo $this->category->title;?></small>
					<?php endif; ?>
				</h2>
			</div>
		<?php endif; ?>
		
		<?php if ($this->params->get('show_description') && $this->category->description) : ?>
			<p><?php echo JHtml::_('content.prepare', $this->category->description, '', 'com_content.category'); ?></p>
		<?php endif; ?>
	</div>
	<?php endif; ?>

<div class="grid blog isotope clearfix <?php echo $this->pageclass_sfx;?> <?php echo $grid_cls ?>" id="grid">
	<div class="item grid-sizer"></div>
	<?php if ($this->params->get('show_tags', 1) && !empty($this->category->tags->itemTags)) :
        $this->category->tagLayout = new JLayoutFile('joomla.content.tags');
        echo $this->category->tagLayout->render($this->category->tags->itemTags);
    endif; ?>
	
	<?php if (empty($this->lead_items) && empty($this->link_items) && empty($this->intro_items)) :
        if ($this->params->get('show_no_articles', 1)) : ?>
			<p><?php echo JText::_('COM_CONTENT_NO_ARTICLES'); ?></p>
		<?php endif; ?>
	<?php endif; ?>

	<?php
		$introcount = (count($this->intro_items));
		$counter = 0;
	?>

	<?php if (!empty($this->intro_items)) : ?>
	<?php foreach ($this->intro_items as $key => &$item) : ?>
	  <?php
		$this->item = &$item;
		echo $this->loadTemplate('item');
		?>
	<?php endforeach; ?>
	<?php endif; ?>

</div>

<?php echo JLayoutHelper::render('joomla.content.pagination', array('params'=>$this->params, 'pagination'=>$this->pagination)); ?>

</div>
