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
$show_section_title = $aparams->get('show_section_title', 0);

// get news
$catids = $aparams->get('list_categories');
$categories = count($catids) ? JATemplateHelper::loadCategories($catids) : JATemplateHelper::getCategories();

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

$isAjax = ($app->input->get('t3action') == 'module');
$listId = 'magazine-category-module-'.$module->id;

// get global values
$show_intro = $aparams->get('show_intro');
$show_category = $aparams->get('show_intro_category');
$show_readmore = $aparams->get('show_intro_readmore');
$show_hits = $aparams->get('show_hits');                           
$show_author = $aparams->get('show_author');
$show_publish_date = $aparams->get('show_publish_date');
$show_create_date = $aparams->get('show_create_date');
$show_intro_text  = $aparams->get('show_intro_text');
?>

<?php if(!$isAjax): ?>
<div class="magazine-featured">
	<!-- MAGAZINE LISTING -->
	<div class="magazine-featured-items">
		<?php if($show_section_title) : ?>
			<div class="magazine-section-heading videos-section-heading">
				<h4><?php echo $section_title; ?></h4>
			</div>
		<?php endif; ?>

		<div class="magazine-intro magazine-featured-intro" id="<?php echo $listId; ?>">
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
          $aparams->set('show_intro_category', $show_category);
    			$aparams->set('show_hits', $show_hits);
    			$aparams->set('show_author', $show_author);
    			$aparams->set('show_publish_date', $show_publish_date);
          $aparams->set('show_create_date', $show_create_date);
          $aparams->set('show_intro',$show_intro_text);
          $aparams->set('show_intro_readmore',$show_readmore);
          $aparams->set('intro_block_position', 0);
					foreach ($items as $item) : ?>
						<?php if ($i % $cols == 0): /* start new row */ ?>
							<div class="row row-articles  equal-height equal-height-child">
						<?php endif ?>
						<div class="col col-xs-12 col-sm-6 col-md-<?php echo $col_width ?> magazine-item video-item">
              <article>
							 <?php echo JATemplateHelper::render($item, 'joomla.content.intro', array('item' => $item, 'params' => $aparams, 'img-size' => 'medium')); ?>
              </article>
						</div>
						<?php if (++$i % $cols == 0 || $i == $t): /* close row */ ?>
							</div>
						<?php endif ?>
					<?php endforeach; ?>

				<?php else: ?>

					<div class="row-articles equal-height equal-height-child">
						<?php
						$t = count($items);
						$c = $cols;
						$n = ceil($t / $c);
						$i = 0;
            $aparams->set('show_intro_category', $show_category);
      			$aparams->set('show_hits', $show_hits);
      			$aparams->set('show_author', $show_author);
      			$aparams->set('show_publish_date', $show_publish_date);
            $aparams->set('show_create_date', $show_create_date);
            $aparams->set('show_intro',$show_intro_text);
            $aparams->set('show_intro_readmore',$show_readmore);
            $aparams->set('intro_block_position', 0);
						?>
						<?php foreach ($items as $item) : ?>
							<?php if ($i == 0): /* start new col */ ?>
								<div class="col col-xs-12 col-sm-6 col-md-<?php echo $col_width ?> magazine-item">
                <article>
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
                </article>
								</div>
							<?php endif ?>
						<?php endforeach; ?>
					</div>

				<?php endif ?>

<?php if(!$isAjax): ?>
			</div>
			<?php if($limit && count($items) == $limit): ?>
				<div class="load-more">
					<button class="btn btn-default btn-info" data-link="<?php echo JUri::getInstance()->toString(); ?>" data-maxpage="<?php echo $maxPage; ?>" onclick="jActions.loadModuleNextPage(this, <?php echo $module->id; ?>, '<?php echo $listId; ?>', function(){JAVideoPlayer.playlist();}); return false;" title="<?php echo JText::_('Load More'); ?>">
						<?php echo JText::_('TPL_LOAD_MORE'); ?>
						<span class="fa fa-spin fa-circle-o-notch" style="display: none"></span>
					</button>
				</div>
			<?php endif; ?>
	</div>
	<!-- //MAGAZINE LISTING -->

</div>
<?php endif; ?>