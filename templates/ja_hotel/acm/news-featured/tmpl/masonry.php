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
$aparams->loadArray ($helper->toArray(true));
// get featured items
$catid = $aparams->get ('catid', 1);

$display_items = $aparams->get ('featured_intro', 3);
$limit = (int) $aparams->get('highlight_limit', 0);
$maxPage = 10;//only get 10 pages by default
if($limit) {
	if($display_items) {
		$maxPage = ceil($display_items / $limit);
	}
	$display_items = $limit;
}

$intro_columns = $aparams->get ('featured_intro_columns', 3);

$items = JATemplateHelper::getArticles($aparams, $catid, $display_items);

// get global values
$show_intro = $aparams->get('show_intro');
$show_category = $aparams->get('show_category');
$show_readmore = $aparams->get('show_readmore');
$show_hits = $aparams->get('show_hits');                           
$show_author = $aparams->get('show_author');
$show_publish_date = $aparams->get('show_publish_date');
$block_position = $aparams->get('block_position');
$leading_title = $aparams->get('leading_title');
$show_leading_title = $aparams->get('show_leading_title');
$section_info  = $aparams->get('section_info');
$section_link  = $aparams->get('section_link'); 

$isAjax = ($app->input->get('t3action') == 'module');
$listId = 'magazine-category-module-'.$module->id;     
?>
<?php if(!$isAjax): ?>
<div class="grid magazine-featured">

	<div class="magazine-featured-items">

			<?php
			$aparams->set('show_category', $aparams->get('show_link_category', $show_category));
			$aparams->set('show_hits', $aparams->get('show_link_hits', $show_hits));
			$aparams->set('show_author', $aparams->get('show_link_author', $show_author));
			$aparams->set('show_publish_date', $aparams->get('show_link_publish_date', $show_publish_date));
			$aparams->set('block_position', $aparams->get('link_block_position', $block_position));
		?>
		<div class="magazine-intro magazine-featured-intro" id="<?php echo $listId; ?>">
<?php endif; ?>
			<?php 
        $intro_index = 0;
        $intro_count = count($items); 
        if ($show_leading_title) : $intro_count = $intro_count + 1; endif;
      	$n = ceil($intro_count / $intro_columns); 
        $r = 1;
        $e = 1;
      ?>
			<?php foreach ($items as $item) : ?>  
				<?php if($intro_index % $intro_columns == 0) : ?>
					<div class="row row-articles equal-height equal-height-child">
				<?php $r++; endif ?>
				<div class="magazine-item col col-sm-<?php echo round((12 / $intro_columns)) ?>" itemscope itemtype="http://schema.org/Article">
          <article>
					<?php echo JATemplateHelper::render($item, 'joomla.content.intro', array('item' => $item, 'params' => $aparams)); ?>
          </article>
				</div>
				<?php $intro_index++; ?>
        
				<?php if(($intro_index % $intro_columns == 0) || $intro_index == $intro_count) : ?>
				  </div>
        <?php  
				endif; ?>
			<?php endforeach; ?>
      
      <?php if ($show_leading_title) : ?>
    		<div class="magazine-section-heading magazine-item col col-sm-<?php echo round((12 / $intro_columns)) ?>">
          <article>
            <div class="logo-text">
      				<a title=".hotel" href="#">
      					<span>.hotel</span>
      				</a>
      			</div>
    			  <h4 class="section-title"><?php echo $leading_title; ?></h4>
            <div class="section-info"><?php echo $section_info; ?></div>
            <?php if($section_link): ?><a href="<?php echo $section_link; ?>" class="btn btn-border">View all</a><?php endif; ?>
          </article>
        </div>
      </div> <?php // end row-articles ?>
  	  <?php endif; ?>
<?php if(!$isAjax): ?>
	</div>
      <?php if($limit && count($items) == $limit): ?>
				<div class="load-more">
					<button class="btn btn-default btn-info" data-link="<?php echo JUri::getInstance()->toString(); ?>" data-maxpage="<?php echo $maxPage; ?>" onclick="jActions.loadModuleNextPage(this, <?php echo $module->id; ?>, '<?php echo $listId; ?>', function(){JAVideoPlayer.playlist();}); return false;" title="<?php echo JText::_('TPL_LOAD_MORE'); ?>">
						<?php echo JText::_('TPL_LOAD_MORE'); ?>
						<span class="fa fa-spin fa-circle-o-notch" style="display: none"></span>
					</button>
				</div>
			<?php endif; ?> 
</div>
<?php endif; ?>