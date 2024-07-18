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

$limit = JFactory::getApplication()->getCfg('list_limit', 0);

if ($displayData['config']->get('enable_slideshow_search', 1) == 1) :
	JFactory::getDocument()->addScriptDeclaration('
	Solidres.jQuery(function($){
		$(".carousel").carousel();
	});
');
endif;

if (empty($displayData['items'])) : ?>
<div class="alert alert-block">
	<?php echo JText::_('SR_HUB_NOTHING_FOUND') ?>
</div>
<?php else :
$t = 0;
$total = count($displayData['items']);
$html = '';
$shownItems = 0;
foreach ($displayData['items'] as $key => $item) :
	if (count($item->roomTypes) == 0) continue;
	if (!empty($displayData['searchConditions']['prices'])) :
		$shownItems ++;
	endif;
	if ($t == 0 && $t % 3 == 0) :
		echo '<div class="row asset-row asset-row-grid equal-height equal-height-child">';
	elseif ($t % 3 == 0) :
		echo '</div><div class="row asset-row asset-row-grid equal-height equal-height-child">';
	endif;
  
  $deepLink = 'index.php?option=com_solidres&Itemid='.$displayData['itemid'].'&task=reservationasset.checkavailability&id='.$item->id.'&checkin='.$displayData['searchConditions']['checkIn'].'&checkout='.$displayData['searchConditions']['checkOut'].'&room_quantity='.$displayData['searchConditions']['roomQuantity'];
	for ($r = 1, $rCount = count($displayData['searchConditions']['roomsOccupancyOptions']); $r <= $rCount; $r++) :
		$deepLink .=
			"&room_opt[$r][adults]={$displayData['searchConditions']['roomsOccupancyOptions'][$r]['adults']}".
			"&room_opt[$r][children]={$displayData['searchConditions']['roomsOccupancyOptions'][$r]['children']}";
	endfor;
	$deepLink = JRoute::_($deepLink);
?>
	<div class="col col-sm-4">
		<div class="asset-item">
		<?php if( !empty($item->media) ) :
			echo '<div id="carousel'.$item->id.'" class="carousel slide carousel-fade">';
			echo '<div class="carousel-inner">';
			$countMedia = 0;
			$active = '';
			foreach ($item->media as $media) :
				$active = ($countMedia == 0) ? 'active' : '';
				?>
				<div class="item <?php echo $active ?>">
					<a class="room_type_details sr-photo-<?php echo $item->id ?>" href="<?php echo $deepLink; ?>">
						<img src="<?php echo SRURI_MEDIA.'/assets/images/system/thumbnails/1/'.$media->value; ?>"
							 alt="<?php echo $media->name ?>"/>
					</a>
				</div>
				<?php
				$countMedia ++;
			endforeach;
			echo '</div>';
			echo '<a class="carousel-control left" href="#carousel'.$item->id.'" data-slide="prev">&lsaquo;</a>';
			echo '<a class="carousel-control right" href="#carousel'.$item->id.'" data-slide="next">&rsaquo;</a>';
			echo '</div>';
		endif; ?>
		<h4 class="asset-title">
					<a href="<?php echo $deepLink ?>"><?php echo $item->name ?></a>
					<div class="asset-rate">
						<?php for ($i = 0; $i < $item->rating; $i++ ) : ?>
							<i class="rating fa fa-star"></i>
						<?php endfor; ?>
					</div>
				</h4>
				
				<div class="asset-subinfo">
					<span class="address_1 reservation_asset_subinfo">
						<?php echo $item->address_1;?>
						<a class="show_location_map" href="<?php echo JRoute::_('index.php?option=com_solidres&task=map.show&id='.$item->id) ?>">
							<i class="fa fa-map-marker"></i>
						</a>
					</span>
				</div>

				<div class="room-type-list">
				<?php
				if (count($item->roomTypes) > 0) :
					foreach($item->roomTypes as $roomType ) :
						?>
						<div class="room-type-row">
							<p><span class="label label-danger">
							<?php echo (int)$roomType->occupancy_adult + (int)$roomType->occupancy_child; ?>
									<i class="fa uk-icon-user fa-user"></i>
							</span>
							<span class="room-name"><?php echo $roomType->name ?></span>
							<?php if ($roomType->featured == 1) : ?>
								<span class="label label-success"><?php echo JText::_('SR_FEATURED_ROOM_TYPE') ?></span>
							<?php endif ?></p>
							<?php
									// Loop through all available tariffs for this search
							if (isset($roomType->availableTariffs) && count($roomType->availableTariffs) > 0) :
								// We only show the first tariff
								$firstTariff = reset($roomType->availableTariffs);
								$id = key($roomType->availableTariffs);
								$tariffSuffix = '';
								if ($firstTariff['tariffType'] == 0 || $firstTariff['tariffType'] == 2) :
									$tariffSuffix .= JText::_('SR_TARIFF_SUFFIX_PER_ROOM');
								else :
									$tariffSuffix .= JText::_('SR_TARIFF_SUFFIX_PER_PERSON');
								endif;

								$tariffSuffix .= JText::plural('SR_TARIFF_SUFFIX_NIGHT_NUMBER', $displayData['numberOfNights']);
								?>

								<small id="tariff_val_<?php echo $id ?>" class="tariff_val">
									<?php echo $firstTariff['val']->format() . ' ' . $tariffSuffix ?>
								</small>

							<?php
							endif
							?>
						</div> <!-- end of .row-fluid -->
					<?php
					endforeach;
				endif ?>
			</div>
		</div>
	</div> <!-- end of .span4 -->
<?php if ($t == ($total - 1)) : ?>
</div>
<?php endif;
	$t ++;
endforeach
?>

	<?php
	// Show empty message, useful in case of price filtering
	if (!empty($displayData['searchConditions']['prices'])) :
		if ($shownItems == 0) : ?>
			<div class="alert alert-block">
				<?php echo JText::_('SR_HUB_NOTHING_FOUND') ?>
			</div>
		<?php endif;
	endif ?>

	<?php
	$showPagination = true;
	// If prices filtering is activated, the pagination need a special treatment
	// When we are in page 2+, do not hide pagination
	if (!empty($displayData['searchConditions']['prices'])) :
		if ($shownItems < $limit && $displayData['pagination']->limitstart == 0 ) :
			$showPagination = false;
		endif;
	endif;

	if ($showPagination) : ?>
		<div class="pagination">
			<?php echo $displayData['pagination']->getPagesLinks(); ?>
		</div>
	<?php endif ?>
<?php endif ?>