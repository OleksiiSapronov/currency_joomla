<?php
/*------------------------------------------------------------------------
  Solidres - Hotel booking extension for Joomla
  ------------------------------------------------------------------------
  @Author    Solidres Team
  @Website   http://www.solidres.com
  @Copyright Copyright (C) 2013 - 2015 Solidres. All Rights Reserved.
  @License   GNU General Public License version 3, or later
------------------------------------------------------------------------*/

defined('_JEXEC') or die;

$uri = JUri::getInstance();
$config = JFactory::getConfig();
$tzoffset = $config->get('offset');
$timezone = new DateTimeZone($tzoffset);

?>
<div id="solidres">
<div class="navbar dashboard-nav">
	<div class="navbar-inner">
		<ul class="nav">
			<li class="active">
				<a href="<?php echo JRoute::_('index.php?option=com_solidres&view=customer') ?>">
					<?php echo JText::_('SR_CUSTOMER_DASHBOARD_MY_RESERVATIONS') ?>
				</a>
			</li>
			<li>
				<a href="<?php echo JRoute::_('index.php?option=com_solidres&task=myprofile.edit&id=' . $this->modelReservations->getState('filter.customer_id')) . '&return=' . base64_encode($uri) ?>">
					<?php echo JText::_('SR_CUSTOMER_DASHBOARD_MY_PROFILE') ?>
				</a>
			</li>
		</ul>
	</div>
</div>

<form action="<?php echo JUri::getInstance()->toString() ?>" method="post" name="adminForm" id="adminForm">
	<div class="row-fluid">
		<div class="span4">
			<select class="span12" name="filter_published" onchange="document.getElementById('adminForm').submit()">
				<option value=""><?php echo JText::_('SR_CUSTOMER_DASHBOARD_FILTER_ALL_STATUSES') ?></option>
				<option <?php echo $this->modelReservations->getState('filter.state') == 4 ? 'selected' : '' ;?> value="4"><?php echo JText::_('SR_CUSTOMER_DASHBOARD_FILTER_CANCELLED_STATUSES') ?></option>
			</select>
		</div>
		<div class="span4">
			<select class="span12" name="filter_location" onchange="document.getElementById('adminForm').submit()">
				<option value=""><?php echo JText::_('SR_CUSTOMER_DASHBOARD_FILTER_ALL_CITIES') ?></option>
				<?php
				foreach ($this->filterLocations as $location) :
					$selected = '';
					if (strtolower($this->modelReservations->getState('filter.location')) == strtolower($location['city'])) :
						$selected = 'selected';
					endif;
				?>
				<option <?php echo $selected ?> value="<?php echo $location['city'] ?>"><?php echo $location['city'] ?></option>
				<?php
				endforeach;
				?>
			</select>
		</div>
		<div class="span4">
			<select class="span12" name="filter_reservation_asset_id" onchange="document.getElementById('adminForm').submit()">
				<option value=""><?php echo JText::_('SR_CUSTOMER_DASHBOARD_FILTER_ALL_ASSETS') ?></option>
				<?php
				foreach ($this->filterAssets as $asset) :
					$selected = '';
					if (strtolower($this->modelReservations->getState('filter.reservation_asset_id')) == strtolower($asset['id'])) :
						$selected = 'selected';
					endif;
					?>
					<option <?php echo $selected ?> value="<?php echo $asset['id'] ?>"><?php echo $asset['name'] ?></option>
				<?php
				endforeach;
				?>
			</select>
		</div>
	</div>
	<input type="hidden" name="filter_clear" value="0" />
</form>

<?php
if (!empty($this->reservations)) : echo '<div class="reservation-list">';
	foreach ($this->reservations as $reservation) :
		// Caching is needed
		$tempAssetId = 0;
		if ($reservation->reservation_asset_id != $tempAssetId) :
			$asset = $this->modelAsset->getItem($reservation->reservation_asset_id);
		endif;
?>
<div class="row reservation-row"><div class="asset-item">
	<div class="col-sm-3">
		<div class="inner-skip-right sr-align-center">
			<img class="" src="<?php echo SRURI_MEDIA.'/assets/images/system/thumbnails/1/'.$asset->media[0]->value; ?>" alt="<?php echo $asset->name ?>" />
		</div>
	</div>
	<div class="col-sm-5 reservationasset-desc">
		<div class="inner-skip-left">
			<h3>
				<a href="<?php echo JRoute::_('index.php?option=com_solidres&view=reservationasset&id=' . $asset->id)?>">
					<?php echo $asset->name ?>
				</a>

				<?php for ($i = 1; $i <= $asset->rating; $i++) : ?>
					<i class="rating icon-star uk-icon-star fa-star"></i>
				<?php endfor ?>
			</h3>

			<?php
			echo $asset->address_1 .', '.
			     (!empty($asset->postcode) ? $asset->postcode.', ' : '').
			     (!empty($asset->city) ? $asset->city.', ' : '').
			     $asset->country_name
			?>
			<a class="show_map" href="<?php echo JRoute::_('index.php?option=com_solidres&task=map.show&id='.$asset->id) ?>">
				<?php echo JText::_('SR_SHOW_MAP') ?>
			</a>

			<p class="actions">
				<a class="btn btn-small" href="<?php echo JText::_('index.php?option=com_solidres&task=myreservation.edit&Itemid='.$this->itemid.'&id='. $reservation->id.'&return='.base64_encode($uri))?>">
					<?php echo JText::_('SR_MANAGE_BOOKING') ?>
				</a>
			</p>
		</div>
	</div>
	<div class="col-sm-4 checkinout">
		<div class="row-fluid">
			<div class="span6">
				<div class="inner">
					<span class="dayt">
					<?php echo JDate::getInstance($reservation->checkin, $timezone)->format('l', true) ?>
				</span>
				<span class="dayn">
					<?php echo JDate::getInstance($reservation->checkin, $timezone)->format('j', true) ?>
				</span>
				<span class="montht">
					<?php echo JDate::getInstance($reservation->checkin, $timezone)->format('F', true) ?>
				</span>
				<span class="yearn">
					<?php echo JDate::getInstance($reservation->checkin, $timezone)->format('Y', true) ?>
				</span>
				</div>
			</div>
			<div class="span6">
				<div class="inner">
					<span class="dayt">
					<?php echo JDate::getInstance($reservation->checkout, $timezone)->format('l', true) ?>
				</span>
				<span class="dayn">
					<?php echo JDate::getInstance($reservation->checkout, $timezone)->format('j', true) ?>
				</span>
				<span class="montht">
					<?php echo JDate::getInstance($reservation->checkout, $timezone)->format('F', true) ?>
				</span>
				<span class="yearn">
					<?php echo JDate::getInstance($reservation->checkout, $timezone)->format('Y', true) ?>
				</span>
				</div>
			</div>
		</div>
	</div>
</div></div>
<?php
	endforeach;
  echo '</div>';
endif;
?>

<div class="row-fluid">
	<?php echo $this->pagination->getListFooter(); ?>
</div>
</div>