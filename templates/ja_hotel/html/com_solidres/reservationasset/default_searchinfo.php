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

$dateCheckIn = JDate::getInstance();
$dateCheckOut = JDate::getInstance();
$showDateInfo = !empty($this->checkin) && !empty($this->checkout);
?>

<div id="availability-search">
	<?php if ($this->checkin && $this->checkout) : ?>
	<div class="alert alert-info availability-search-info clearfix">
		<?php
		/*if ( $this->item->totalOccupancyMax >= ($this->item->roomsOccupancyOptionsAdults + $this->item->roomsOccupancyOptionsChildren) ) :
			echo JText::sprintf('SR_ROOM_AVAILABLE_FROM_TO',
				$this->item->totalAvailableRoom,
				JDate::getInstance($this->checkin, $this->timezone)->format($this->dateFormat, true) ,
				JDate::getInstance($this->checkout, $this->timezone)->format($this->dateFormat, true),
				$this->item->roomsOccupancyOptionsAdults,
				$this->item->roomsOccupancyOptionsChildren
			);
		endif;*/

		if ($this->item->roomsOccupancyOptionsAdults == 0 && $this->item->roomsOccupancyOptionsChildren == 0) :
			echo JText::sprintf('SR_ROOM_AVAILABLE_FROM_TO_MSG4',
				$this->item->totalAvailableRoom,
				JDate::getInstance($this->checkin, $this->timezone)->format($this->dateFormat, true) ,
				JDate::getInstance($this->checkout, $this->timezone)->format($this->dateFormat, true)
			);
		else :
			if ($this->item->totalOccupancyMax >= ($this->item->roomsOccupancyOptionsAdults + $this->item->roomsOccupancyOptionsChildren)) :
				if ($this->item->totalAvailableRoom >= $this->item->roomsOccupancyOptionsCount) :
					echo JText::sprintf('SR_ROOM_AVAILABLE_FROM_TO_MSG1',
						$this->item->totalAvailableRoom,
						JDate::getInstance($this->checkin, $this->timezone)->format($this->dateFormat, true) ,
						JDate::getInstance($this->checkout, $this->timezone)->format($this->dateFormat, true),
						$this->item->roomsOccupancyOptionsAdults,
						$this->item->roomsOccupancyOptionsChildren
					);
				else:
					echo JText::sprintf('SR_ROOM_AVAILABLE_FROM_TO_MSG2',
						$this->item->totalAvailableRoom,
						JDate::getInstance($this->checkin, $this->timezone)->format($this->dateFormat, true) ,
						JDate::getInstance($this->checkout, $this->timezone)->format($this->dateFormat, true),
						$this->item->roomsOccupancyOptionsAdults,
						$this->item->roomsOccupancyOptionsChildren
					);
				endif;
			else :
				echo JText::sprintf('SR_ROOM_AVAILABLE_FROM_TO_MSG3',
					JDate::getInstance($this->checkin, $this->timezone)->format($this->dateFormat, true) ,
					JDate::getInstance($this->checkout, $this->timezone)->format($this->dateFormat, true),
					$this->item->roomsOccupancyOptionsAdults,
					$this->item->roomsOccupancyOptionsChildren
				);

			endif;
		endif;
		?>
		<a class="btn btn-default btn-sm pull-right" href="<?php echo JRoute::_('index.php?option=com_solidres&task=reservationasset.startOver&id='. $this->item->id ) ?>"><i class="icon-remove uk-icon-refresh fa-refresh"></i> <?php echo JText::_('SR_SEARCH_RESET')?></a>
	</div>
	<?php endif; ?>

	<form id="sr-checkavailability-form-component"
		  action="<?php echo JRoute::_('index.php', false)?>"
		  method="GET"
		>
		<input name="id" value="<?php echo $this->item->id ?>" type="hidden" />
		<input name="Itemid" value="<?php echo $this->itemid ?>" type="hidden" />

		<input type="hidden"
			   name="checkin"
			   value="<?php echo isset($this->checkin) ? $this->checkin : $dateCheckIn->add(new DateInterval('P'.($this->minDaysBookInAdvance).'D'))->setTimezone($this->timezone)->format('d-m-Y', true) ?>"
			   />

		<input type="hidden"
			   name="checkout"
			   value="<?php echo isset($this->checkout) ? $this->checkout : $dateCheckOut->add(new DateInterval('P'.($this->minDaysBookInAdvance + $this->minLengthOfStay).'D'))->setTimezone($this->timezone)->format('d-m-Y', true) ?>"
			   />
		<input type="hidden" name="option" value="com_solidres" />
		<input type="hidden" name="task" value="reservationasset.checkavailability" />
		<input type="hidden" name="ts" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>

