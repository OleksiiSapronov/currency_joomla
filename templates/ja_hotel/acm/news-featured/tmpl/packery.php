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
$show_intro_text  = $aparams->get('show_intro_text');
$leading_title = $aparams->get('leading_title');
$show_leading_title = $aparams->get('show_leading_title');
$section_info  = $aparams->get('section_info');
$section_link  = $aparams->get('section_link');

$doc = JFactory::getDocument();
$doc->addStyleSheet (T3_TEMPLATE_URL . '/css/isotope.css');
$doc->addScript (T3_TEMPLATE_URL . '/js/jquery.infinitescroll.min.js');
$doc->addScript (T3_TEMPLATE_URL . '/js/imagesloaded.pkgd.min.js');
$doc->addScript (T3_TEMPLATE_URL . '/js/isotope.pkgd.min.js');
$doc->addScript (T3_TEMPLATE_URL . '/js/packery-mode.pkgd.min.js');
$sitename = $doc->params->get('sitename');
if (!$sitename) {
	$sitename = JFactory::getConfig()->get('sitename');
}
$siteUrl  = JURI::base(true);
?>

<div class="magazine-featured ja-isotope-wrap packery">

	<div class="magazine-featured-items">

		<?php if ($intro_count = count ($intro)): $counter=0; ?>
		<!-- Intro -->
			<?php
			$aparams->set('show_intro_category', $show_category);
			$aparams->set('show_hits', 0);
			$aparams->set('show_author', 0);
			$aparams->set('show_publish_date', 0);
      $aparams->set('show_create_date', 0);
      $aparams->set('show_intro',$show_intro_text);
      $aparams->set('show_intro_readmore',$show_readmore);
      $aparams->set('intro_block_position', 1);
		?>
		<div id="grid" class="magazine-intro magazine-featured-intro grid isotope clearfix grid-xs-1 grid-smx-1 grid-sm-2 grid-md-2 grid-lg-4">
      <?php if ($show_leading_title) : $counter=1; ?>
    		<div class="magazine-section-heading magazine-item item height2">
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
  	  <?php endif; ?>
      
			<?php 
        $intro_index = 0; 
      ?>
			<?php foreach ($intro as $item) : ?>
				<div class="magazine-item item <?php if($counter==0 || $counter==2 || $counter==4 || $counter==5): echo 'height2'; endif; ?> hidden-xs" itemscope itemtype="http://schema.org/Article">
          <article>
					<?php echo JATemplateHelper::render($item, 'joomla.content.intro', array('item' => $item, 'params' => $aparams)); ?>
          </article>
				</div>
				<?php $intro_index++; $counter++; ?>
			<?php endforeach; ?>
      
		</div>
		<!-- // Intro -->
		<?php endif ?>

	</div> <!-- //Left Column -->
</div>