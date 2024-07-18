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
$count_leading = 0;
$count_intro   = $aparams->get ('featured_intro', 3);
$count_links   = $aparams->get ('featured_links', 5);
$intro_columns = $aparams->get ('featured_intro_columns', 3);
$featured_count = $count_leading + $count_intro + $count_links;
$leading       = $intro = $links = array();

$items = JATemplateHelper::getArticles($aparams, $catid, $featured_count);

$i = 0;
foreach ($items as &$item) {

	if ($i < $count_leading) {
		$leading[] = $item;
	} elseif ($i < $count_leading + $count_intro) {
		$intro[] = $item;
	} else {
		$links[] = $item;
	}

	$i++;
}

// get global values
$show_intro = $aparams->get('show_intro');
$show_category = $aparams->get('show_intro_category');
$show_readmore = $aparams->get('show_intro_readmore');
$show_hits = $aparams->get('show_hits');                           
$show_author = $aparams->get('show_author');
$show_publish_date = $aparams->get('show_publish_date');
$show_create_date = $aparams->get('show_create_date');
$show_intro_text  = $aparams->get('show_intro_text');
$leading_title = $aparams->get('leading_title');
$show_leading_title = $aparams->get('show_leading_title');
$section_info  = $aparams->get('section_info');
$section_link  = $aparams->get('section_link');

$doc = JFactory::getDocument();
$sitename = $doc->params->get('sitename');
if (!$sitename) {
	$sitename = JFactory::getConfig()->get('sitename');
}
$siteUrl  = JURI::base(true);       
?>

<div class="grid magazine-featured">

	<div class="magazine-featured-items">

		<?php if ($intro_count = count ($intro)): ?>
		<!-- Intro -->
			<?php
			$aparams->set('show_intro_category', $show_category);
			$aparams->set('show_hits', $show_hits);
			$aparams->set('show_author', $show_author);
			$aparams->set('show_publish_date', $show_publish_date);
      $aparams->set('show_create_date', $show_create_date);
      $aparams->set('show_intro',$show_intro_text);
      $aparams->set('show_intro_readmore',$show_readmore);
      $aparams->set('intro_block_position', 0);
		?>
		<div class="magazine-intro magazine-featured-intro">
			<?php 
        $intro_index = 0; 
        if ($show_leading_title) : $intro_count = $intro_count + 1; endif;
      	$n = ceil($intro_count / $intro_columns); 
        $r = 1;
        $e = 1;
      ?>
			<?php foreach ($intro as $item) : ?>  
				<?php if($intro_index % $intro_columns == 0) : ?>
					<div class="row row-articles <?php if($r==$n): echo 'last-row'; endif; ?> equal-height equal-height-child">
				<?php $r++; endif ?>
				<div class="magazine-item col col-xs-12 col-sm-6 col-md-<?php echo round((12 / $intro_columns)) ?> hidden-xs" itemscope itemtype="http://schema.org/Article">
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
    		<div class="magazine-section-heading magazine-item col col-xs-12 col-sm-6 col-md-<?php echo round((12 / $intro_columns)) ?>">
          <article>
            <div class="logo-text">
      				<a title="<?php echo $sitename; ?>" href="<?php echo $siteUrl; ?>">
      					<span><?php echo $sitename; ?></span>
      				</a>
      			</div>
    			  <h4 class="section-title"><?php echo $leading_title; ?></h4>
            <div class="section-info"><?php echo $section_info; ?></div>
            <?php if($section_link): ?><a href="<?php echo $section_link; ?>" class="btn btn-border">View all</a><?php endif; ?>
          </article>
        </div>
      </div> <?php // end row-articles ?>
  	  <?php endif; ?>
      
		</div>
		<!-- // Intro -->
		<?php endif ?>

	</div> <!-- //Left Column -->
</div>