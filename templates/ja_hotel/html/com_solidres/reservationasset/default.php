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

if (!isset($this->item->params['only_show_reservation_form']))
{
	$this->item->params['only_show_reservation_form'] = 0;
}
$fbStars = '';
for ($i = 1; $i <= $this->item->rating; $i++) :
	$fbStars .= '&#x2605;';
endfor;

$this->document->addCustomTag('<meta property="og:title" content="'.$fbStars . ' ' . $this->item->name . ', ' . $this->item->city . ', ' . $this->item->country_name .'"/>');
$this->document->addCustomTag('<meta property="og:type" content="place"/>');
$this->document->addCustomTag('<meta property="og:url" content="'.JRoute::_('index.php?option=com_solidres&view=reservationasset&id='.$this->item->id, true, true).'"/>');
$this->document->addCustomTag('<meta property="og:image" content="' . SRURI_MEDIA.'/assets/images/system/thumbnails/1/'.$this->item->media[0]->value . '"/>');
$this->document->addCustomTag('<meta property="og:image" content="' . SRURI_MEDIA.'/assets/images/system/thumbnails/1/'.$this->item->media[1]->value . '"/>');
$this->document->addCustomTag('<meta property="og:image" content="' . SRURI_MEDIA.'/assets/images/system/thumbnails/1/'.$this->item->media[2]->value . '"/>');
$this->document->addCustomTag('<meta property="og:site_name" content="'.JFactory::getConfig()->get( 'sitename' ).'"/>');
$this->document->addCustomTag('<meta property="og:description" content="'.strip_tags($this->item->description).'"/>');
$this->document->addCustomTag('<meta property="place:location:latitude"  content="'.$this->item->lat.'" />');
$this->document->addCustomTag('<meta property="place:location:longitude" content="'.$this->item->lng.'" /> ');
?>
<div class="row">
	<div class="col-sm-12">
		<div id="solidres">
			<div class="reservation_asset_item clearfix">
			<div class="asset-ct-top">
				<?php if ($this->item->params['only_show_reservation_form'] == 0 ) : ?>
					<h3 class="asset-title">
						<?php echo $this->escape($this->item->name); ?>
						<?php for ($i = 1; $i <= $this->item->rating; $i++) : ?>
						<i class="rating icon-star uk-icon-star fa-star"></i>
						<?php endfor ?>
					</h3>

					<div class="asset-subinfo-group">
						<span class="address_1 reservation_asset_subinfo">
							<?php
								echo $this->item->address_1 .', '.
								(!empty($this->item->postcode) ? $this->item->postcode.', ' : '').
								(!empty($this->item->city) ? $this->item->city.', ' : '').
								$this->item->country_name
							?>
							<a class="show_map" href="<?php echo JRoute::_('index.php?option=com_solidres&task=map.show&id='.$this->item->id) ?>">
								<?php echo JText::_('SR_SHOW_MAP') ?>
							</a>
						</span>

						<span class="address_2 reservation_asset_subinfo">
							<?php echo $this->item->address_2;?>
						</span>

						<span class="phone reservation_asset_subinfo">
							<?php echo JText::_('SR_PHONE') .': '. $this->item->phone;?>
						</span>

						<span class="fax reservation_asset_subinfo">
							<?php echo JText::_('SR_FAX') .': '. $this->item->fax;?>
						</span>
					</div>

					<span class="social_network reservation_asset_subinfo clearfix">
						<?php
							if ( !empty($this->item->reservationasset_extra_fields['facebook_link'])
									&& $this->item->reservationasset_extra_fields['facebook_show']== 1) : ?>
						<a href="<?php echo $this->item->reservationasset_extra_fields['facebook_link'];?>" target="_blank"><i class="fa fa-facebook"></i></a>
						<?php	endif;
						?>
						<?php
							if ( !empty($this->item->reservationasset_extra_fields['twitter_link'])
									&& $this->item->reservationasset_extra_fields['twitter_show']== 1) : ?>
						<a href="<?php echo $this->item->reservationasset_extra_fields['twitter_link'];?>" target="_blank"><i class="fa fa-twitter"></i></a>
						<?php	endif;
						?>
						<?php
							if ( !empty($this->item->reservationasset_extra_fields['linkedin_link'])
									&& $this->item->reservationasset_extra_fields['linkedin_show']== 1) : ?>
						<a href="<?php echo $this->item->reservationasset_extra_fields['linkedin_link'];?>" target="_blank"><i class="fa fa-linkedin"></i></a>
						<?php	endif;
						?>
						<?php
							if ( !empty($this->item->reservationasset_extra_fields['gplus_link'])
									&& $this->item->reservationasset_extra_fields['gplus_show']== 1) : ?>
						<a href="<?php echo $this->item->reservationasset_extra_fields['gplus_link'];?>" target="_blank"><i class="fa fa-google-plus"></i></a>
						<?php	endif;
						?>
						<?php
							if ( !empty($this->item->reservationasset_extra_fields['tumblr_link'])
									&& $this->item->reservationasset_extra_fields['tumblr_show']== 1) : ?>
						<a href="<?php echo $this->item->reservationasset_extra_fields['tumblr_link'];?>" target="_blank"><i class="fa fa-tumblr"></i></a>
						<?php	endif;
						?>
						<?php
							if ( !empty($this->item->reservationasset_extra_fields['foursquare_link'])
									&& $this->item->reservationasset_extra_fields['foursquare_show']== 1) : ?>
						<a href="<?php echo $this->item->reservationasset_extra_fields['foursquare_link'];?>" target="_blank"><i class="fa fa-foursquare"></i></a>
						<?php	endif;
						?>
						<?php
							if ( !empty($this->item->reservationasset_extra_fields['myspace_link'])
									&& $this->item->reservationasset_extra_fields['myspace_show']== 1) : ?>
						<a href="<?php echo $this->item->reservationasset_extra_fields['myspace_link'];?>" target="_blank"><i class="fa fa-myspace"></i></a>
						<?php	endif;
						?>
						<?php
							if ( !empty($this->item->reservationasset_extra_fields['pinterest_link'])
									&& $this->item->reservationasset_extra_fields['pinterest_show']== 1) : ?>
						<a href="<?php echo $this->item->reservationasset_extra_fields['pinterest_link'];?>" target="_blank"><i class="fa fa-pinterest"></i></a>
						<?php	endif;
						?>
						<?php
							if ( !empty($this->item->reservationasset_extra_fields['slideshare_link'])
									&& $this->item->reservationasset_extra_fields['slideshare_show']== 1) : ?>
						<a href="<?php echo $this->item->reservationasset_extra_fields['slideshare_link'];?>" target="_blank"><i class="fa fa-slideshare"></i></a>
						<?php	endif;
						?>
						<?php
							if ( !empty($this->item->reservationasset_extra_fields['vimeo_link'])
									&& $this->item->reservationasset_extra_fields['vimeo_show']== 1) : ?>
						<a href="<?php echo $this->item->reservationasset_extra_fields['vimeo_link'];?>" target="_blank"><i class="fa fa-vimeo"></i></a>
						<?php	endif;
						?>
						<?php
							if ( !empty($this->item->reservationasset_extra_fields['youtube_link'])
									&& $this->item->reservationasset_extra_fields['youtube_show']== 1) : ?>
						<a href="<?php echo $this->item->reservationasset_extra_fields['youtube_link'];?>" target="_blank"> <i class="fa fa-youtube"></i></a>
						<?php	endif;
						?>
					</span>

				<?php echo $this->defaultGallery; ?>

				<div class="asset-desc"><?php echo $this->item->description;?></div>

				<?php endif ?>
				</div>

				<!--<div class="row">
					<div class="col-sm-12">
						<?php /*echo $this->loadTemplate('searchinfo'); */?>
					</div>
				</div>-->

				<div class="row">
					<div class="col-sm-12">
						<?php echo $this->loadTemplate('roomtype'); ?>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-12">
						<?php echo $this->loadTemplate('information'); ?>
					</div>
	            </div>

				<?php if ($this->showPoweredByLink) : ?>
				<p>Powered by <a target="_blank" title="Solidres - A hotel booking extension for Joomla" href="http://www.solidres.com">Solidres</a></p>
				<?php endif ?>
			</div>
		</div>
	</div>
</div>