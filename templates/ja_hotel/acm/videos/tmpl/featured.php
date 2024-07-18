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
// get featured items
$catid = $aparams->get('catid', 1);
$count_leading = $aparams->get('featured_leading', 5);
//$count_intro = $aparams->get('featured_intro', 3);
$count_intro = 0;
//$count_links = $aparams->get('featured_links', 5);
$count_links = 0;
$intro_columns = $aparams->get('featured_intro_columns', 3);
$featured_count = $count_leading + $count_intro + $count_links;
$leading = $intro = $links = array();
$leading_title = $aparams->get('leading_title');
$extra_link = $aparams->get('extra_link');
$leading_auto_play = $aparams->get('leading_auto_play');
$show_leading_title = $aparams->get('show_leading_title');
$block_links_title = $aparams->get('block_links_title');
$show_block_links_title = $aparams->get('show_block_links_title');

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
$show_category = $aparams->get('show_category');
$show_readmore = $aparams->get('show_readmore');
$show_hits = $aparams->get('show_hits');
$show_author = $aparams->get('show_author');
$show_publish_date = $aparams->get('show_publish_date');
$block_position = $aparams->get('block_position');
$animation_type = $aparams->get('animation_type', 'slide');
?>

<div class="videos-featured">

	<div class="col videos-featured-items">
		<?php if ($show_leading_title || $extra_link) : ?>
			<div class="magazine-section-heading videos-section-heading col-md-6">
				<?php if ($show_leading_title) : ?>
					<h4><?php echo $leading_title; ?></h4>
				<?php endif ?>

				<?php if($extra_link) : ?>
					<a href="<?php echo $extra_link; ?>" title="More">More <i class="fa fa-arrow-right"></i></a>
				<?php endif ?>
			</div>
		<?php endif; ?>

		<?php if (count($leading)): ?>
			<!-- Leading -->
			<?php
			$aparams->set('show_intro_category', 1);
			$aparams->set('show_hits', 0);
			$aparams->set('show_author', 0);
			$aparams->set('show_publish_date', 0);
      $aparams->set('show_create_date', 0);
			?>
			<div class="videos-featured-list col-md-6 <?php if($count_leading > 5) echo 'has-scroll'?> hidden-xs">
				<ul>
					<?php
					$i = 0;
					foreach ($leading as $item) : ?>
					<li class="video-item">
            <div class="magazine-item-media videos-item-media">
            	<?php echo JLayoutHelper::render('joomla.content.image.videolist', array('item' => $item, 'params' => $aparams)); ?>
            </div>
            <div class="magazine-item-main videos-item-main">
              <a class="video-title" href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($item->id, $item->catid)); ?>" ><?php echo htmlspecialchars($item->title); ?></a>
            </div>
					</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<!-- //Leading -->
		<?php endif ?>
    
    <div class="player-wrap col-md-6">
			<div id="ja-main-player">
				<?php if (count($leading)): ?>
					<?php echo JLayoutHelper::render('joomla.content.video_play', array('item' => $leading[0], 'context' => 'featured')); ?>
				<?php endif ?>
			</div>

			<script type="text/javascript">

				(function($){
					$(document).ready(function(){
						$('#ja-main-player').find('iframe.ja-video, video, .jp-video, .jp-jplayer').each(function(){
							var container = $('#ja-main-player');
							var width = container.outerWidth(true);
							var height = container.outerHeight(true);

							$(this).removeAttr('width').removeAttr('height');
							$(this).css({width: width, height: height});
						});
					});
				})(jQuery);
			</script>
		</div>

	</div>
	<!-- //Left Column -->

	</div>