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
				$("#sr-checkavailability-form input[name=\'checkout\']").val($.datepicker.formatDate("yy-mm-dd", $(this).datepicker("getDate")));
				$("#sr-checkavailability-form .checkout_module").text($.datepicker.formatDate("'.$jsDateFormat.'", $(this).datepicker("getDate")));
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

				$("#sr-checkavailability-form input[name=\'checkin\']").val($.datepicker.formatDate("yy-mm-dd", currentSelectedDate));
				$("#sr-checkavailability-form input[name=\'checkout\']").val($.datepicker.formatDate("yy-mm-dd", checkoutMinDate));

				$("#sr-checkavailability-form .checkin_module").text($.datepicker.formatDate("'.$jsDateFormat.'", currentSelectedDate));
				$("#sr-checkavailability-form .checkout_module").text($.datepicker.formatDate("'.$jsDateFormat.'", checkoutMinDate));
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
?>

<form id="sr-checkavailability-form" action="<?php echo JRoute::_('index.php#form', false)?>" method="GET" class="form-stacked sr-validate">
    <fieldset>
        <input name="id" value="<?php echo $tableAsset->id ?>" type="hidden" />
	    <div class="row-fluid">
		    <div class="span12">
			    <label for="checkin">
				    <?php echo JText::_('SR_SEARCH_CHECKIN_DATE')?>
			    </label>
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
		</div>
	    <div class="row-fluid">
		    <div class="span12">
			    <label for="checkout">
				    <?php echo JText::_('SR_SEARCH_CHECKOUT_DATE')?>
			    </label>
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
	    </div>

		<?php if ($params->get('enable_room_quantity_option', 0)) : ?>
		<div class="row-fluid">
			<div class="span12">
				<label><?php echo JText::_('SR_SEARCH_ROOMS') ?></label>
				<select class="span12 room_quantity" name="room_quantity">
					<?php for ($room_num = 1; $room_num <= $maxRooms; $room_num ++) : ?>
						<option <?php echo $room_num == $roomsOccupancyOptionsCount ? 'selected' : '' ?> value="<?php echo $room_num  ?>"><?php echo $room_num  ?></option>
					<?php endfor ?>
				</select>
			</div>
		</div>


		<?php for ($room_num = 1; $room_num <= $maxRooms; $room_num ++) : ?>
	    <div class="row-fluid">
		    <div class="span12 room_num_row" id="room_num_row_<?php echo $room_num ?>" style="<?php echo $room_num > 0 ? 'display: none' : '' ?>">
			    <div class="row-fluid">
				    <div class="span4">
					    <label>&nbsp;</label>
					    <?php echo JText::_('SR_SEARCH_ROOM') ?> <?php echo $room_num  ?>
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
						    for ($c = 0; $c < $maxChildren; $c ++) :
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
		</div>
		<?php endfor; ?>
	    <?php endif; ?>
	    <div class="row-fluid">
		    <div class="span12">
			    <div class="action">
				    <button class="btn btn-block btn-primary" type="submit"><i class="icon-search uk-icon-search"></i> <?php echo JText::_('SR_SEARCH')?></button>
			    </div>
		    </div>
		</div>
    </fieldset>

    <input type="hidden" name="option" value="com_solidres" />
    <input type="hidden" name="task" value="reservationasset.checkavailability" />
	<input type="hidden" name="Itemid" value="<?php echo $params->get('target_itemid') ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>