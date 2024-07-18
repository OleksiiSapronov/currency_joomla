<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$print = $displayData['print'];
$item = $displayData['item'];
$params = $item->params;
$positions = $params->get('info_block_position', 0);

$useDefList =
	($params->get('show_modify_date') ||
		$params->get('show_publish_date') ||
		$params->get('show_create_date') ||
		$params->get('show_hits') ||
		$params->get('show_category') ||
		$params->get('show_parent_category') ||
		$params->get('show_author'));
$icons = $params->get('show_print_icon') || $params->get('show_email_icon');

$tplparams = JFactory::getApplication()->getTemplate(true)->params;


?>
<article class="article" itemscope itemtype="http://schema.org/Article">
	<meta itemprop="inLanguage" content="<?php echo ($item->language === '*') ? JFactory::getConfig()->get('language') : $item->language; ?>" />
	<meta itemprop="url" content="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid)) ?>" />
	<?php if ($params->get('show_title')) : ?>
		<?php echo JLayoutHelper::render('joomla.content.item_title', array('item' => $item, 'params' => $params, 'title-tag'=>'h1')); ?>
	<?php endif; ?>
	<?php if ($icons || $print || ($useDefList && in_array($positions, array(0, 2)))) : ?>
		<aside class="article-aside article-aside-full">
			<?php if ($useDefList && in_array($positions, array(0, 2))) : ?>
			<?php echo JLayoutHelper::render('joomla.content.info_block.magazine_block', array('item' => $item, 'params' => $params, 'position' => 'above')); ?>
			<?php endif ?>
      <?php echo $item->event->afterDisplayTitle; ?>
			<?php if ($print): ?>
				<div id="pop-print" class="hidden-print">
					<?php echo JHtml::_('icon.print_screen', $item, $params); ?>
				</div>
			<?php endif ?>
      
      <?php if ($icons && !$print): ?>
        <?php echo JLayoutHelper::render('joomla.content.magazine_icons', array('item' => $item, 'params' => $params)); ?>
			<?php endif; ?>
		</aside>
	<?php endif; ?>

  <?php if(json_decode($item->images)->image_intro): ?>
	<section class="article-intro-media">
		<?php echo JLayoutHelper::render('joomla.content.image.intro', $displayData); ?>
		<?php $title = $item->category_title; ?>
	</section>
  <?php endif; ?>

	<section class="row article-navigation top">
		<?php if (isset ($item->pagination)) echo $item->pagination ?>
	</section>

	<section class="article-full">

		<div class="article-content-main">
		<?php echo $item->event->beforeDisplayContent; ?>
    
    <?php if ($params->get('show_vote')) :
      if (isset($item->rating_sum) && $item->rating_count > 0) {
        $item->rating = round($item->rating_sum / $item->rating_count, 1);
        $item->rating_percentage = $item->rating_sum / $item->rating_count * 20;
      } else {
        if (!isset($item->rating)) $item->rating = 0;
        if (!isset($item->rating_count)) $item->rating_count = 0;
        $item->rating_percentage = $item->rating * 20;
      }
      $uri = JUri::getInstance();
      
      ?>
      <div itemtype="http://schema.org/AggregateRating" itemscope itemprop="aggregateRating" class="rating-info pd-rating-info">
        <form class="rating-form" method="POST" action="<?php echo htmlspecialchars($uri->toString()) ?>">
          <ul class="rating-list">
            <li class="rating-current" style="width:<?php echo $item->rating_percentage; ?>%;"></li>
            <li><a href="#" title="<?php echo JText::_('JA_1_STAR_OUT_OF_5'); ?>" class="one-star">1</a></li>
            <li><a href="#" title="<?php echo JText::_('JA_2_STARS_OUT_OF_5'); ?>" class="two-stars">2</a></li>
            <li><a href="#" title="<?php echo JText::_('JA_3_STARS_OUT_OF_5'); ?>" class="three-stars">3</a></li>
            <li><a href="#" title="<?php echo JText::_('JA_4_STARS_OUT_OF_5'); ?>" class="four-stars">4</a></li>
            <li><a href="#" title="<?php echo JText::_('JA_5_STARS_OUT_OF_5'); ?>" class="five-stars">5</a></li>
          </ul>
          <div class="rating-log">(<meta itemprop="bestRating" content="5" /><span itemprop="ratingValue"><?php echo $item->rating ?></span> / <span itemprop="ratingCount"><?php echo $item->rating_count; ?></span> votes)</div>
          <input type="hidden" name="task" value="article.vote" />
          <input type="hidden" name="hitcount" value="0" />
          <input type="hidden" name="user_rating" value="5" />
          <input type="hidden" name="url" value="<?php echo htmlspecialchars($uri->toString()) ?>" />
          <?php echo JHtml::_('form.token') ?>
        </form>
      </div>
      
      <script type="text/javascript">
        !function($){
          $('.rating-form').each(function(){
            var form = this;
            $(this).find('.rating-list li a').click(function(event){
              event.preventDefault();
              form.user_rating.value = this.innerHTML;
              form.submit();
            });
          });
        }(window.jQuery);
      </script>
    <?php endif; ?>

		<section class="article-content" itemprop="articleBody">
			<?php echo JLayoutHelper::render('joomla.content.info_block.topic', array('item' => $item)); ?>
			<?php echo $item->text; ?>
			
			<?php if ($params->get('show_tags', 1) && !empty($item->tags)) : ?>
				<?php echo JLayoutHelper::render('joomla.content.tags', $item->tags->itemTags); ?>
			<?php endif; ?>
		</section>

		<?php if ($useDefList && in_array($positions, array(1, 2))) : ?>
			<footer class="article-footer">
				<?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $item, 'params' => $params, 'position' => 'below')); ?>
			</footer>
		<?php endif; ?>
		
		<?php echo $item->event->afterDisplayContent; ?>

		</div>
	</section>

	<section class="row article-navigation bottom">
		<?php if (isset ($item->pagination)) echo $item->pagination ?>
	</section>

</article>
