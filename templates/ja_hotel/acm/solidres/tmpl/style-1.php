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

$catid = $aparams->get('catid', 1);
$count_leading = 0;
$count_intro = $aparams->get('featured_intro',3);
$intro_columns = $aparams->get('featured_intro_columns', 3);
$faetured_count = $count_leading + $count_intro;
$leading = $intro = $links = array();

$items = JATemplateHelper::getAssets($aparams, $catid);
    
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
$show_intro = $aparams->get('show_intro_text');
$show_category = $aparams->get('show_intro_category');
$show_readmore = $aparams->get('show_intro_readmore');
$show_rating = $aparams->get('show_rating');

?>

<div class="solidres magazine-featured">

    <div class="magazine-featured-items">

        <?php if($intro_count = count($intro)) :?>
        <!-- Intro -->
        <div class="magazine-intro magazine-featured-intro">
            <?php
              $intro_index = 0;
              $n = ceil($intro_count / $intro_columns);
              $r = 1;
              $e = 1;
            ?>
            
            <?php foreach($intro as $item) :  
              $images   = json_decode($item->images);
              $name     = $item->name;
              $address  = $item->address_1;
              $link     = $item->link;
              $desc     = $item->description;
              $limit    = $aparams->get('intro_limit');
              $category = $item->category_title;
              $catLink  = $item->link.'&amp;categories='.$item->catslug;

              if (strlen($desc) > $limit)
              {
                  $offset = ($limit - 3) - strlen($desc);
                  $desc = substr($desc, 0, strrpos($desc, ' ', $offset)) . '...';
              }
              $rate     = $item->rating;
              $hits     = $item->hits;
            ?>
            <?php if($intro_index % $intro_columns == 0) : ?>
            <div class="row row-article <?php if($r==$n): echo 'last-row'; endif; ?> equal-height equal-height-child">
                <?php $r++; endif ?>
                <div class="magazine-item col col-sm-<?php echo round((12/$intro_columns)) ?>" itemscope itemtype="http://schema.org/Article">
                    <article>
                        <?php if (isset($images->image_intro) && !empty($images->image_intro)) { ?>
                        <div class="magazine-item-media">
                          <div class="pull-left item-image">
                          	<img itemprop="thumbnailUrl" alt="" src="<?php echo $images->image_intro; ?>">
                          </div>
                        </div>
                        <?php } ?>
                        
                        <div class="magazine-item-main">
                          
                          <?php if($show_category): ?>
                          <span title="Category: " class="category-name">
				                    <a href="<?php echo $catLink; ?>"><span itemprop="genre"><?php echo $category; ?></span></a>			
                          </span>
                          <?php endif; ?>
                          <div class="article-title">
              							<h3>
              									<a href="<?php echo $link; ?>"><?php echo $name; ?></a>
                                <?php if($show_rating): ?>
                                  <?php for ($i=0; $i<$rate; $i++) : ?>
                                      <i class="rating fa fa-star"></i>
                                  <?php endfor; ?>
                                <?php endif; ?>
              							</h3>
              						</div>
                          <?php if($show_intro): ?>
                          <div class="magazine-item-ct"><?php echo $desc; ?></div>
                          <?php endif; ?>
                          
                          <?php if($show_readmore): ?>
                          <section class="readmore">
			                       <a href="<?php echo $link; ?>" class="btn btn-default"><span>Book now</span></a>
		                      </section>
                          <?php endif; ?>
                        </div>
                    </article>
                </div>
                <?php $intro_index++; ?>

                <?php if($intro_index % $intro_columns == 0 || $intro_index == $intro_count) :  ?>
                    </div>
            <?php endif; ?>
            <?php endforeach; ?>
        </div> <?php // end row-articles ?>
    <?php endif; ?>

    </div>
    <!-- // Intro -->
</div>