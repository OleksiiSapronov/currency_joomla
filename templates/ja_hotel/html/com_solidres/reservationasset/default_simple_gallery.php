<?php 
/*------------------------------------------------------------------------
  Solidres - Hotel booking extension for Joomla
  ------------------------------------------------------------------------
  @Author    Solidres Team
  @Website   http://www.solidres.com
  @Copyright Copyright (C) 2013 - 2014 Solidres. All Rights Reserved.
  @License   GNU General Public License version 3, or later
------------------------------------------------------------------------*/

defined('_JEXEC') or die;

?>
<div class="asset-gallery">
  <div class="row">
    <?php if( !empty($this->item->media) ) : ?>
      <div class="col-sm-6">
          <div class="main-photo">
            <a class="sr-photo" href="<?php echo SRURI_MEDIA.'/assets/images/system/'.$this->item->media[0]->value; ?>">
              <img width="" src="<?php echo SRURI_MEDIA.'/assets/images/system/thumbnails/1/'.$this->item->media[0]->value; ?>" />
            </a>
          </div>
      </div>
    <?php endif; ?>

      <div class=" col-sm-6">
        <div class="other-photos clearfix">
          <?php foreach ($this->item->media as $media) : ?>
            <a class="sr-photo" href="<?php echo SRURI_MEDIA.'/assets/images/system/'.$media->value; ?>">
                <img class="photo" src="<?php echo SRURI_MEDIA.'/assets/images/system/thumbnails/2/'.$media->value; ?>" />
            </a>
        <?php endforeach ?>
        </div>
      </div>
  </div>
</div>