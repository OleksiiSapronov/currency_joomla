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

JHtml::_('behavior.framework');
$config = JFactory::getConfig();
$solidresUtilities = SRFactory::get('solidres.utilities.utilities');
$tzoffset = $config->get('offset');
$timezone = new DateTimeZone($tzoffset);
$dateCheckIn = JDate::getInstance();
$dateCheckOut = JDate::getInstance();
$solidresConfig = JComponentHelper::getParams('com_solidres');
$minDaysBookInAdvance = $solidresConfig->get('min_days_book_in_advance', 0);
$maxDaysBookInAdvance = $solidresConfig->get('max_days_book_in_advance', 0);
$minLengthOfStay = $solidresConfig->get('min_length_of_stay', 1);
$datePickerMonthNum = $solidresConfig->get('datepicker_month_number', 3);
$weekStartDay = $solidresConfig->get('week_start_day', 1);
$dateFormat = $solidresConfig->get('date_format', 'd-m-Y');
$jsDateFormat = $solidresUtilities::convertDateFormatPattern($dateFormat);
$roomsOccupancyOptionsCount = count($roomsOccupancyOptions);
$maxRooms = $params->get('max_room_number', 10);
$maxAdults = $params->get('max_adult_number', 10);
$maxChildren = $params->get('max_child_number', 10);
$isOptional = $params->get('location_optional', 0);
$enableAutocomplete = $params->get('location_autocomplete', 0);

// These variables are used to set the defaultDate of datepicker
$defaultCheckinDate = isset($checkin) ? JDate::getInstance($checkin, $timezone)->format('Y-m-d', true) : '';
$defaultCheckoutDate = isset($checkout) ? JDate::getInstance($checkout, $timezone)->format('Y-m-d', true) : '';
if (!empty($defaultCheckinDate)) :
	$defaultCheckinDateArray = explode('-', $defaultCheckinDate);
	$defaultCheckinDateArray[1] -= 1; // month in javascript is less than 1 in compare with month in PHP
endif;

if (!empty($defaultCheckoutDate)) :
	$defaultCheckoutDateArray = explode('-', $defaultCheckoutDate);
	$defaultCheckoutDateArray[1] -= 1; // month in javascript is less than 1 in compare with month in PHP
endif;


$doc = JFactory::getDocument();
JHtml::_('script', SRURI_MEDIA.'/assets/js/datePicker/localization/jquery.ui.datepicker-'.JFactory::getLanguage()->getTag().'.js', false, false);
$doc->addScriptDeclaration('
	Solidres.jQuery(function($) {
		var minLengthOfStay = '.$minLengthOfStay.';
		var checkout = $(".checkout_datepicker_inline_module").datepicker({
			minDate : "+' . ( $minDaysBookInAdvance + $minLengthOfStay ). '",
			numberOfMonths : '.$datePickerMonthNum.',
			showButtonPanel : true,
			dateFormat : "'.$jsDateFormat.'",
			firstDay: '.$weekStartDay.',
			' . (isset($checkout) ? 'defaultDate: new Date(' . implode(',' , $defaultCheckoutDateArray) .'),' : '') . '
			onSelect: function() {
				$("#sr-advancedsearch-form input[name=\'checkout\']").val($.datepicker.formatDate("yy-mm-dd", $(this).datepicker("getDate")));
				$("#sr-advancedsearch-form .checkout_module").text($.datepicker.formatDate("'.$jsDateFormat.'", $(this).datepicker("getDate")));
				$(".checkout_datepicker_inline_module").slideToggle();
				$(".checkin_module").removeClass("disabledCalendar");
			}
		});
		var checkin = $(".checkin_datepicker_inline_module").datepicker({
			minDate : "+' .  $minDaysBookInAdvance . 'd",
			'.($maxDaysBookInAdvance > 0 ? 'maxDate: "+'. ($maxDaysBookInAdvance) . '",' : '' ).'
			numberOfMonths : '.$datePickerMonthNum.',
			showButtonPanel : true,
			dateFormat : "'.$jsDateFormat.'",
			'. (isset($checkin) ? 'defaultDate: new Date(' . implode(',' , $defaultCheckinDateArray) .'),' : '') . '
			onSelect : function() {
				var currentSelectedDate = $(this).datepicker("getDate");
				var checkoutMinDate = $(this).datepicker("getDate", "+1d");
				checkoutMinDate.setDate(checkoutMinDate.getDate() + minLengthOfStay);
				checkout.datepicker( "option", "minDate", checkoutMinDate );
				checkout.datepicker( "setDate", checkoutMinDate);
				
				$("#sr-advancedsearch-form input[name=\'checkin\']").val($.datepicker.formatDate("yy-mm-dd", currentSelectedDate));
				$("#sr-advancedsearch-form input[name=\'checkout\']").val($.datepicker.formatDate("yy-mm-dd", checkoutMinDate));

				$("#sr-advancedsearch-form .checkin_module").text($.datepicker.formatDate("'.$jsDateFormat.'", currentSelectedDate));
				$("#sr-advancedsearch-form .checkout_module").text($.datepicker.formatDate("'.$jsDateFormat.'", checkoutMinDate));
				$(".checkin_datepicker_inline_module").slideToggle();
				$(".checkout_module").removeClass("disabledCalendar");
			},
			firstDay: '.$weekStartDay.'
		});
		$(".ui-datepicker").addClass("notranslate");
		$(".checkin_module").click(function() {
			if (!$(this).hasClass("disabledCalendar")) {
				$(".checkin_datepicker_inline_module").slideToggle("slow", function() {
					if ($(this).is(":hidden")) {
						$(".checkout_module").removeClass("disabledCalendar");
					} else {
						$(".checkout_module").addClass("disabledCalendar");
					}
				});
			}
		});

		$(".checkout_module").click(function() {
			if (!$(this).hasClass("disabledCalendar")) {
				$(".checkout_datepicker_inline_module").slideToggle("slow", function() {
					if ($(this).is(":hidden")) {
						$(".checkin_module").removeClass("disabledCalendar");
					} else {
						$(".checkin_module").addClass("disabledCalendar");
					}
				});
			}
		});

		$(".room_quantity").change(function() {
			var curQuantity = $(this).val();
			$(".room_num_row").each(function( index ) {
				var index2 = index + 1;
				if (index2 <= curQuantity) {
					$("#room_num_row_" + index2).show();
					$("#room_num_row_" + index2 + " select").removeAttr("disabled");
				} else {
					$("#room_num_row_" + index2).hide();
					$("#room_num_row_" + index2 + " select").attr("disabled", "disabled");
				}
			});
		});

		if ($(".room_quantity").val() > 0) {
			$(".room_quantity").trigger("change");
		}
    });
');

if ($enableAutocomplete) :
	$doc->addScriptDeclaration('
		Solidres.jQuery(function($) {
			$("#location").typeahead({
				"source": '.json_encode($cityList).'
			})
		});
	');
endif;
?>
<form id="sr-advancedsearch-form" action="<?php echo JRoute::_('index.php', false)?>" method="GET" class="form-stacked sr-validate solidres-module-advancedsearch form-inline home-module">
	<fieldset>
		<div class="form-group location">
			<?php if ($params->get('location_field_type', 'text') == 'text') : ?>
			<input type="text"
				   name="location"
				   id="location"
				   class="form-control"
           placeholder="<?php echo JText::_('SR_SEARCH_LOCATION')?>"
				   <?php echo $enableAutocomplete ? 'autocomplete="off"' : '' ?>
				   value="<?php echo !empty($searchConditions['city']) ? $searchConditions['city'] : ''?>"
					<?php echo $isOptional ? '' : 'required' ?>
				/>
			<?php else : ?>
			<select name="location" id="location" class="span12" <?php echo $isOptional ? '' : 'required' ?>>
				<?php
				$selectValues = $params->get('location_field_values', '');
				if (!empty($selectValues)) :
				$selectValues = explode(PHP_EOL, $selectValues);
				endif;

				if ($isOptional) :
				?>
					<option value=""></option>
				<?php
				endif;

				if (is_array($selectValues)) :
					foreach ($selectValues as $option) :
						$option = trim(preg_replace('/\s\s+/', ' ', $option));
				?>
					<option <?php echo $option == $searchConditions['city'] ? 'selected' : '' ?> value="<?php echo $option ?>"><?php echo $option ?></option>
				<?php
					endforeach;
				endif;
				?>
			</select>
			<?php endif ?>
		</div>

		<div class="form-group">
			<div class="checkin_module datefield">
				<?php echo isset($checkin) ?
					JDate::getInstance($checkin, $timezone)->format($dateFormat, true) :
					$dateCheckIn->add(new DateInterval('P'.($minDaysBookInAdvance).'D'))->setTimezone($timezone)->format($dateFormat, true) ?>
			</div>
			<div class="checkin_datepicker_inline_module datepicker_inline" style="display: none"></div>
			<?php // this field must always be "Y-m-d" as it is used internally only ?>
			<input type="hidden" name="checkin" value="<?php echo isset($checkin) ?
				JDate::getInstance($checkin, $timezone)->format('Y-m-d', true) :
				$dateCheckIn->add(new DateInterval('P'.($minDaysBookInAdvance).'D'))->setTimezone($timezone)->format('Y-m-d', true) ?>" />
		</div>

		<div class="form-group">
			<div class="checkout_module datefield">
				<?php echo isset($checkout) ?
					JDate::getInstance($checkout, $timezone)->format($dateFormat, true) :
					$dateCheckOut->add(new DateInterval('P'.($minDaysBookInAdvance + $minLengthOfStay).'D'))->setTimezone($timezone)->format($dateFormat, true)
				?>
			</div>
			<div class="checkout_datepicker_inline_module datepicker_inline" style="display: none"></div>
			<?php // this field must always be "Y-m-d" as it is used internally only ?>
			<input type="hidden" name="checkout" value="<?php echo isset($checkout) ?
				JDate::getInstance($checkout, $timezone)->format('Y-m-d', true) :
				$dateCheckOut->add(new DateInterval('P'.($minDaysBookInAdvance + $minLengthOfStay).'D'))->setTimezone($timezone)->format('Y-m-d', true) ?>" />
		</div>

		<?php if ($params->get('enable_room_quantity_option', 0)) : ?>
		<div class="form-group">
			<select class="span12 room_quantity" name="room_quantity">
				<?php for ($room_num = 1; $room_num <= $maxRooms; $room_num ++) : ?>
					<option <?php echo $room_num == $roomsOccupancyOptionsCount ? 'selected' : '' ?> value="<?php echo $room_num  ?>"><?php echo $room_num  ?></option>
				<?php endfor ?>
			</select>
		</div>
    <?php endif; ?>
    
    <button class="btn btn-primary" type="submit"><i class="icon-search"></i> <?php echo JText::_('SR_SEARCH')?></button>
    
    <?php if ($params->get('enable_room_quantity_option', 0)) : ?>
		<div class="form-group choose-room">
		<?php for ($room_num = 1; $room_num <= $maxRooms; $room_num ++) : ?>
			<div class="room_num_row" id="room_num_row_<?php echo $room_num ?>" style="<?php echo $room_num > 0 ? 'display: none' : '' ?>">
				<div class="row-fluid">
					<div class="span4">
						<label>&nbsp;</label>
						<?php echo JText::_('SR_SEARCH_ROOM') ?> <?php echo $room_num ?>
					</div>
					<div class="span4">
						<label><?php echo JText::_('SR_SEARCH_ROOM_ADULTS') ?></label>
						<select <?php echo $room_num > 0 ? 'disabled': '' ?> class="span12" name="room_opt[<?php echo $room_num ?>][adults]">
							<?php
							for ($a = 1; $a <= $maxAdults; $a ++) :
								$selected = '';
								if (isset($roomsOccupancyOptions[$room_num]['adults'])
								    &&
								    ($a == $roomsOccupancyOptions[$room_num]['adults'])
								) :
									$selected = 'selected';
								endif;
								?>
								<option <?php echo $selected ?> value="<?php echo $a ?>"><?php echo $a ?></option>
							<?php
							endfor
							?>
						</select>
					</div>
					<div class="span4">
						<label><?php echo JText::_('SR_SEARCH_ROOM_CHILDREN') ?></label>
						<select <?php echo $room_num > 0 ? 'disabled': '' ?> class="span12" name="room_opt[<?php echo $room_num ?>][children]">
							<?php
							for ($c = 0; $c <= $maxChildren; $c ++) :
								$selected = '';
								if (isset($roomsOccupancyOptions[$room_num]['children'])
								    &&
								    $c == $roomsOccupancyOptions[$room_num]['children']
								) :
									$selected = 'selected';
								endif;
								?>
								<option <?php echo $selected ?> value="<?php echo $c ?>"><?php echo $c ?></option>
							<?php
							endfor
							?>
						</select>
					</div>
				</div>
			</div>
		<?php endfor; ?>
		</div>
		<?php endif; ?>

	</fieldset>

	<input type="hidden" name="option" value="com_solidres" />
	<input type="hidden" name="task" value="hub.search" />
	<input type="hidden" name="Itemid" value="<?php echo $params->get('target_itemid') ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>