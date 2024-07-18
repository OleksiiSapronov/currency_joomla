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

$filterCity = $this->state->get('filter.city');
$filterCategory = $this->state->get('filter.category_id');

$location = JFActory::getApplication()->input->get('location');
if (!empty($location))
{
	$this->document->setTitle(JText::sprintf('SR_HUB_PAGE_TITLE_RESERVATION_ASSETS_IN_LOCATION', $this->model->getState('filter.city')));
	$this->document->addCustomTag('<meta property="og:title" content="'.JText::sprintf('SR_HUB_PAGE_TITLE_RESERVATION_ASSETS_IN_LOCATION', $this->model->getState('filter.city')) .'"/>');
	$this->document->addCustomTag('<meta property="og:type" content="website"/>');
	$this->document->addCustomTag('<meta property="og:url" content="'.JRoute::_('index.php?location='.$location.'&option=com_solidres&task=hub.search', true, true).'"/>');
	$this->document->addCustomTag('<meta property="og:image" content="' . SRURI_MEDIA.'/assets/images/logo_200.png"/>');
	$this->document->addCustomTag('<meta property="og:site_name" content="'.JFactory::getConfig()->get( 'sitename' ).'"/>');
	$this->document->addCustomTag('<meta property="og:description" content="' . JFactory::getConfig()->get( 'MetaDesc' ) . '"/>');
}
?>

<div id="solidres">

	<?php
		if (empty($this->checkIn) && empty($this->checkIn) && empty($filterCity) && empty($filterCategory) ) :
			$config = JFactory::getConfig();
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
			$doc = JFactory::getDocument();
			JHtml::_('script', SRURI_MEDIA.'/assets/js/datePicker/localization/jquery.ui.datepicker-'.JFactory::getLanguage()->getTag().'.js', false, false);
			$doc->addScriptDeclaration('
				Solidres.jQuery(function($) {
					var minLengthOfStay = '.$minLengthOfStay.';
					var checkout = $("#hubcheckout").datepicker({
						minDate : "+' . ( $minDaysBookInAdvance + $minLengthOfStay ). '",
						numberOfMonths : '.$datePickerMonthNum.',
						showButtonPanel : true,
						dateFormat : "dd-mm-yy",
						firstDay: '.$weekStartDay.'
					});
					var checkin = $("#hubcheckin").datepicker({
						minDate : "+' .  $minDaysBookInAdvance . 'd",
						'.($maxDaysBookInAdvance > 0 ? 'maxDate: "+'. ($maxDaysBookInAdvance) . '",' : '' ).'
						numberOfMonths : '.$datePickerMonthNum.',
						showButtonPanel : true,
						dateFormat : "dd-mm-yy",
						onSelect : function() {
							var checkoutMinDate = $(this).datepicker("getDate", "+1d");
							checkoutMinDate.setDate(checkoutMinDate.getDate() + minLengthOfStay);
							checkout.datepicker( "option", "minDate", checkoutMinDate );
							checkout.datepicker( "setDate", checkoutMinDate);

						},
						firstDay: '.$weekStartDay.'
					});
					$(".ui-datepicker").addClass("notranslate");
				});
			');

		?>
		<div class="row-fluid">
			<form id="sr-checkavailability-form" action="<?php echo JRoute::_('index.php', false)?>" method="GET" class="form-stacked sr-validate">
				<fieldset>

						<label for="location">
							<?php echo JText::_('SR_SEARCH_LOCATION')?>
						</label>
						<input type="text" name="location" id="location" class="span12"
							   value="" required/>

						<label for="checkin">
							<?php echo JText::_('SR_SEARCH_CHECKIN_DATE')?>
						</label>

						<input type="text"
							   name="checkin"
							   id="hubcheckin"
							   class="datepicker form-control"
							   readonly="true"
							   value="<?php echo $dateCheckIn->add(new DateInterval('P'.($minDaysBookInAdvance).'D'))->setTimezone($timezone)->format('d-m-Y', true) ?>" required/>

						<label for="checkout">
							<?php echo JText::_('SR_SEARCH_CHECKOUT_DATE')?>
						</label>

						<input type="text"
							   name="checkout"
							   id="hubcheckout"
							   class="datepicker form-control"
							   readonly="true"
							   value="<?php echo $dateCheckOut->add(new DateInterval('P'.($minDaysBookInAdvance + $minLengthOfStay).'D'))->setTimezone($timezone)->format('d-m-Y', true) ?>" required/>

						<div class="action">
							<button class="btn btn-primary" type="submit"><i class="icon-search uk-icon-search fa-search"></i> <?php echo JText::_('SR_SEARCH')?></button>
						</div>

				</fieldset>

				<input type="hidden" name="option" value="com_solidres" />
				<input type="hidden" name="task" value="hub.search" />
				<input type="hidden" name="Itemid" value="<?php echo $this->itemid ?>" />
				<?php echo JHtml::_('form.token'); ?>
			</form>
		</div>

	<?php else : ?>

		<?php echo $this->loadTemplate('navbar') ?>

		<section id="search-results">
			<?php echo $this->loadTemplate('searchresults') ?>
		</section>

		<?php if ($this->showPoweredByLink) : ?>
			<div class="row-fluid">
				<div class="span12 powered">
					<p>
						Powered by <a target="_blank" title="Solidres - A hotel booking extension for Joomla" href="http://www.solidres.com">Solidres</a>
					</p>
				</div>
			</div>
		<?php endif ?>
	<?php endif ?>
</div>

