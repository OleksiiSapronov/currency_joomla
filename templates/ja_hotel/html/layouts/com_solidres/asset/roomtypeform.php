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

$roomTypeId = $displayData['roomTypeId'];
$roomType = $displayData['roomType'];
for ($i = 0; $i < $displayData['quantity']; $i++) :
	$currentRoomIndex = NULL;
	if (isset($displayData['reservationDetails']->room['room_types'][$roomTypeId][$displayData['tariffId']][$i])) :
		$currentRoomIndex = $displayData['reservationDetails']->room['room_types'][$roomTypeId][$displayData['tariffId']][$i];
	endif;

	// Html for adult selection
	$htmlAdultSelection = '';
	$htmlAdultSelection .= '<option value="">' . JText::_('SR_ADULT') . '</option>';

	for ($j = 1; $j <= $displayData['roomType']->occupancy_adult; $j++) :
		$selected = '';
		if (isset($currentRoomIndex['adults_number'])) :
			$selected = $currentRoomIndex['adults_number'] == $j ? 'selected' : '';
		else :
			if ($j == 1) :
				$selected = 'selected';
			endif;
		endif;
		$htmlAdultSelection .= '<option ' . $selected . ' value="' . $j . '">' . JText::plural('SR_SELECT_ADULT_QUANTITY', $j) . '</option>';
	endfor;

	// Html for children selection
	$htmlChildSelection = '';
	$htmlChildrenAges = '';
	if (!isset($displayData['roomType']->params['show_child_option'])) :
		$displayData['roomType']->params['show_child_option'] = 1;
	endif;

	// Only show child option if it is enabled and the child quantity > 0
	if ($displayData['roomType']->params['show_child_option'] == 1 && $displayData['roomType']->occupancy_child > 0) :
		$htmlChildSelection .= '';
		$htmlChildSelection .= '<option value="">' . JText::_('SR_CHILD') . '</option>';

		for ($j = 1; $j <= $displayData['roomType']->occupancy_child; $j++) :
			if (isset($currentRoomIndex['children_number'])) :
				$selected = $currentRoomIndex['children_number'] == $j ? 'selected' : '';
			endif;
			$htmlChildSelection .= '
				<option ' . $selected . ' value="' . $j . '">' . JText::plural('SR_SELECT_CHILD_QUANTITY', $j) . '</option>
			';
		endfor;

		// Html for children ages
		if (isset($currentRoomIndex['children_ages'])) :
			for ($j = 0; $j < count($currentRoomIndex['children_ages']); $j++) :
				$htmlChildrenAges .= '
					<li>
						' . JText::_('SR_CHILD') . ' ' . ($j + 1) . '
						<select name="jform[room_types][' . $roomTypeId . '][' . $displayData['tariffId'] .'][' . $i . '][children_ages][]"
							data-raid="' . $displayData['assetId'] . '"
							data-roomtypeid="' . $roomTypeId . '"
							data-tariffid="' . $displayData['tariffId'] . '"
							data-roomindex="' . $i . '"
							class="span6 child_age_' . $roomTypeId . '_' . $displayData['tariffId'] . '_' . $i . '_' . $j . ' trigger_tariff_calculating"
							required
						>';
				$htmlChildrenAges .= '<option value=""></option>';
				for ($age = 1; $age <= $displayData['childMaxAge']; $age ++) :
					$selectedAge = '';
					if ($age == $currentRoomIndex['children_ages'][$j]) :
						$selectedAge = 'selected';
					endif;
					$htmlChildrenAges .= '<option '.$selectedAge.' value="'.$age.'">'.JText::plural('SR_CHILD_AGE_SELECTION', $age).'</option>';
				endfor;

				$htmlChildrenAges .= '
						</select>
					</li>';
			endfor;
		endif;
	endif;

	// Smoking
	$htmlSmokingOption = '';
	if (!isset($displayData['roomType']->params['show_smoking_option'])) :
		$displayData['roomType']->params['show_smoking_option'] = 1;
	endif;

	if ($displayData['roomType']->params['show_smoking_option'] == 1) :
		$selectedNonSmoking = '';
		$selectedSmoking = '';
		if (isset($currentRoomIndex['preferences']['smoking'])) :
			if ($currentRoomIndex['preferences']['smoking'] == 0) :
				$selectedNonSmoking = 'selected';
			else :
				$selectedSmoking = 'selected';
			endif;
		endif;
		$htmlSmokingOption = '
			<div class="form-group"><select class="form-control" name="jform[room_types][' . $roomTypeId . '][' . $displayData['tariffId'] . '][' . $i . '][preferences][smoking]">
				<option value="">' . JText::_('SR_SMOKING') . '</option>
				<option ' . $selectedNonSmoking . ' value="0">' . JText::_('SR_NON_SMOKING_ROOM') . '</option>
				<option ' . $selectedSmoking . ' value="1">' . JText::_('SR_SMOKING_ROOM') . '</option>
			</select></div>
		';
	endif;
	?>
<div class="room_index_row">
	<div class="room_index_form_heading">
		<h5><?php echo JText::_('SR_ROOM') . ' ' . ($i + 1) ?>: <span
				class="tariff_<?php echo $roomTypeId . '_' . $displayData['tariffId'] . '_' . $i ?>">0</span>
			<i class="icon-help uk-icon-question-circle fa-question-circle complex_tariff_break_down_<?php echo $roomTypeId . '_' . $displayData['tariffId'] . '_' . $i ?>"></i>
		</h5>
	</div>
	<div class="form-inline room_index_form_ct">
		<div class="row">
			<div class="col-sm-9">
					<div class="form-group">
						<select
							data-raid="<?php echo $displayData['assetId'] ?>"
							data-roomtypeid="<?php echo $roomTypeId ?>"
							data-tariffid="<?php echo $displayData['tariffId'] ?>"
							data-roomindex="<?php echo $i ?>"
							name="jform[room_types][<?php echo $roomTypeId ?>][<?php echo $displayData['tariffId'] ?>][<?php echo $i ?>][adults_number]"
							required
							class="form-control occupancy_adult_<?php echo $roomTypeId . '_' . $displayData['tariffId'] . '_' . $i ?> trigger_tariff_calculating">
							<?php echo $htmlAdultSelection ?>
						</select>
					</div>
					<div class="form-group">
						<?php if ($displayData['roomType']->params['show_child_option'] == 1 && $displayData['roomType']->occupancy_child > 0) : ?>
						<select
							data-raid="<?php echo $displayData['assetId'] ?>"
							data-roomtypeid="<?php echo $roomTypeId ?>"
							data-roomindex="<?php echo $i ?>"
							data-tariffid="<?php echo $displayData['tariffId'] ?>"
							name="jform[room_types][<?php echo $roomTypeId ?>][<?php echo $displayData['tariffId'] ?>][<?php echo $i ?>][children_number]"
							class="form-control reservation-form-child-quantity trigger_tariff_calculating occupancy_child_<?php echo $roomTypeId . '_' . $displayData['tariffId'] . '_' . $i ?>">
							<?php echo $htmlChildSelection ?>
						</select>
						<?php endif ?>
					</div>
					<div
						class="form-group child-age-details <?php echo(empty($htmlChildrenAges) ? 'nodisplay' : '') ?>">
						<p><?php echo JText::_('SR_AGE_OF_CHILD_AT_CHECKOUT') ?></p>
						<ul class="unstyled"><?php echo $htmlChildrenAges ?></ul>
					</div>


					<div class="form-group">
						<input name="jform[room_types][<?php echo $roomTypeId ?>][<?php echo $displayData['tariffId'] ?>][<?php echo $i ?>][guest_fullname]"
					   required
					   type="text"
					   value="<?php echo(isset($currentRoomIndex['guest_fullname']) ? $currentRoomIndex['guest_fullname'] : '') ?>"
					   class="form-control"
					   placeholder="<?php echo JText::_('SR_GUEST_NAME') ?>"/>
					</div>
						<?php echo $htmlSmokingOption ?>
					<?php
					foreach ($displayData['extras'] as $extra) :
						$extraInputCommonName = 'jform[room_types][' . $roomTypeId . '][' . $displayData['tariffId'] . '][' . $i . '][extras][' . $extra->id . ']';
						$checked = '';
						$disabledCheckbox = '';
						$disabledSelect = 'disabled="disabled"';
						$alreadySelected = false;
						if (isset($currentRoomIndex['extras'])) :
							$alreadySelected = array_key_exists($extra->id, (array)$currentRoomIndex['extras']);
						endif;

						if ($extra->mandatory == 1 || $alreadySelected) :
							$checked = 'checked="checked"';
						endif;

						if ($extra->mandatory == 1) :
							$disabledCheckbox = 'disabled="disabled"';
							$disabledSelect = 'disabled="disabled"';
						endif;

						if ($alreadySelected && $extra->mandatory == 0) :
							$disabledSelect = '';
						endif;
						?>
						<div class="form-group">
							<input <?php echo $checked ?> 
							<?php echo $disabledCheckbox ?> 
							type="checkbox"
							data-target="extra_<?php echo $displayData['tariffId'] ?>_<?php echo $i ?>_<?php echo $extra->id ?>"/>

							<?php if ($extra->mandatory == 1) : ?>
								<input type="hidden" name="<?php echo $extraInputCommonName ?>[quantity]"
									   value="1"/>
							<?php endif ?>

							<select class=" extra_<?php echo $displayData['tariffId'] ?>_<?php echo $i ?>_<?php echo $extra->id ?>"
									name="<?php echo $extraInputCommonName ?>[quantity]"
								<?php echo $disabledSelect ?>>
								<?php
								for ($quantitySelection = 1; $quantitySelection <= $extra->max_quantity; $quantitySelection++) :
									$checked = '';
									if (isset($currentRoomIndex['extras'][$extra->id]['quantity'])) :
										$checked = ($currentRoomIndex['extras'][$extra->id]['quantity'] == $quantitySelection) ? 'selected' : '';
									endif;
								?>
									<option <?php echo $checked ?> value="<?php echo $quantitySelection ?>"><?php echo $quantitySelection ?></option>
								<?php
								endfor;
								?>
							</select>

							<span data-content="<?php echo $extra->description ?>" class="extra_desc_tips" title="<?php echo $extra->name ?>">
								<?php echo $extra->name . ' (' . $extra->currency->format() . ')' ?>
								<i  class="fa fa-question-circle"></i>
							</span>

						</div>
					<?php
					endforeach;
					?>
				
				
			</div>
			<div class="col-sm-3 text-right">
				<button data-step="room" type="submit" class="btn btn-success">
					<i class="fa fa-angle-right"></i>
					<?php echo JText::_('SR_NEXT') ?>
				</button>
			</div>
		</div>

		</div>
	</div>
</div>
</div>
<?php
endfor;




