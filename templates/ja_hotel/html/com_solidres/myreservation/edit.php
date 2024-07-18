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

JLoader::register('SRCurrency', SRPATH_LIBRARY . '/currency/currency.php');
$baseCurrency = new SRCurrency(0, $this->form->getValue('currency_id'));
$totalExtraPriceTaxIncl = $this->form->getValue('total_extra_price_tax_incl');
$totalExtraPriceTaxExcl = $this->form->getValue('total_extra_price_tax_excl');
$totalExtraPriceTaxExclDisplay = $totalExtraPriceTaxExcl . ' ' . $this->form->getValue('currency_code');
$totalExtraTaxAmount = $totalExtraPriceTaxIncl - $totalExtraPriceTaxExcl;
$totalPaid = $this->form->getValue('total_paid');
$deposit = $this->form->getValue('deposit_amount');

$subTotal = clone $baseCurrency;
$subTotal->setValue($this->form->getValue('total_price_tax_excl'));

$tax = clone $baseCurrency;
$tax->setValue($this->form->getValue('total_price_tax_incl') - $this->form->getValue('total_price_tax_excl'));
$totalExtraPriceTaxExclDisplay = clone $baseCurrency;
$totalExtraPriceTaxExclDisplay->setValue($totalExtraPriceTaxExcl);
$totalExtraTaxAmountDisplay = clone $baseCurrency;
$totalExtraTaxAmountDisplay->setValue($totalExtraTaxAmount);
$grandTotal = clone $baseCurrency;
$grandTotal->setValue($this->form->getValue('total_price_tax_incl') + $totalExtraPriceTaxIncl);
$depositAmount = clone $baseCurrency;
$depositAmount->setValue(isset($deposit) ? $deposit : 0);
$totalPaidAmount = clone $baseCurrency;
$totalPaidAmount->setValue(isset($totalPaid) ? $totalPaid : 0);

$couponCode = $this->form->getValue('coupon_code');
$reservationId = $this->form->getValue('id');
$reservationState = $this->form->getValue('state');
$paymentStatus = $this->form->getValue('payment_status');

$badges = array(
	0 => 'label-pending',
	1 => 'label-info',
	2 => 'label-inverse',
	3 => '',
	4 => 'label-warning',
	5 => 'label-success',
	-2 => 'label-important'
);

$statuses = array(
	0 => JText::_('SR_RESERVATION_STATE_PENDING_ARRIVAL'),
	1 => JText::_('SR_RESERVATION_STATE_CHECKED_IN'),
	2 => JText::_('SR_RESERVATION_STATE_CHECKED_OUT'),
	3 => JText::_('SR_RESERVATION_STATE_CLOSED'),
	4 => JText::_('SR_RESERVATION_STATE_CANCELED'),
	5 => JText::_('SR_RESERVATION_STATE_CONFIRMED'),
	-2 => JText::_('JTRASHED')
);

//SRHtml::_('jquery.editable');
$navBar = new JLayoutFile('hub.navbar');
?>

<div id="solidres">
<div class="row-fluid">
	<div class="span12">
		<?php echo $navBar->render(array('activeview' => $this->getName())) ?>
	</div>
</div>

<div class="row-fluid">
	<div class="span12">
		<?php echo JToolbar::getInstance('solidrestoolbar')->render('solidrestoolbar');; ?>
	</div>
</div>

<div class="row-fluid">
	<div class="span12" id="changedatesform">

	</div>
</div>
<div class="row-fluid">
<div id="sr_panel_right" class="sr_form_view span12">
<div class="row-fluid">
	<div class="span12 reservation-detail-box">
		<h3><?php echo JText::_("SR_GENERAL_INFO")?></h3>
		<div class="row-fluid">
			<div class="span6">

				<ul class="reservation-details">
					<li><label><?php echo JText::_("SR_CODE")?></label>  <span class="label <?php echo $badges[$reservationState] ?>"><?php echo $this->form->getValue('code') ?></span> </li>
					<li><label><?php echo JText::_("SR_RESERVATION_ASSET_NAME")?></label>  <?php echo $this->form->getValue('reservation_asset_name') ?></li>
					<li><label><?php echo JText::_("SR_CHECKIN")?></label>  <?php echo date('d-m-Y', strtotime($this->form->getValue('checkin')))?></li>
					<li><label><?php echo JText::_("SR_CHECKOUT")?></label> <?php echo date('d-m-Y', strtotime($this->form->getValue('checkout')))?></li>
					<li><label><?php echo JText::_("SR_NUMBER_OF_NIGHTS")?></label> <?php echo $this->numberOfNights ?></li>
					<li><label><?php echo JText::_("SR_CREATED_DATE")?></label> <?php echo date('d-m-Y', strtotime($this->form->getValue('created_date')))?></li>
					<li><label><?php echo JText::_("SR_PAYMENT_TYPE")?></label> <?php echo JText::_('SR_PAYMENT_METHOD_' . $this->form->getValue('payment_method_id'))  ?></li>
					<li>
						<label><?php echo JText::_("SR_STATUS")?></label>
						<?php echo $statuses[$reservationState] ?>
					</li>
					<li>
						<label><?php echo JText::_("SR_RESERVATION_PAYMENT_STATUS")?></label>
						<?php echo isset($paymentStatuses[$paymentStatus]) ? $paymentStatuses[$paymentStatus] : 'N/A' ?>
					</li>
					<li>
						<label>
							<?php echo JText::_("SR_NOTES")?>
						</label>
						<?php echo $this->form->getValue('note') ?>
					</li>
				</ul>
			</div>

			<div class="span6">
				<ul class="reservation-details">
					<li><label><?php echo JText::_('SR_RESERVATION_SUB_TOTAL') ?></label> <span><?php echo $subTotal->format() ?></span></li>
					<li><label><?php echo JText::_('SR_RESERVATION_TAX') ?></label> <span><?php echo $tax->format() ?></span></li>
					<li><label><?php echo JText::_('SR_RESERVATION_EXTRA_TAX_EXCL') ?></label> <span><?php echo $totalExtraPriceTaxExclDisplay->format() ?></span></li>
					<li><label><?php echo JText::_('SR_RESERVATION_EXTRA_TAX_AMOUNT') ?></label> <span><?php echo $totalExtraTaxAmountDisplay->format() ?></span></li>
					<li><label><?php echo JText::_('SR_RESERVATION_GRAND_TOTAL') ?></label> <span><?php echo $grandTotal->format() ?></span></li>
					<li><label><?php echo JText::_('SR_RESERVATION_DEPOSIT_AMOUNT') ?></label> <span><?php echo $depositAmount->format() ?></span></li>
					<li>
						<label><?php echo JText::_('SR_RESERVATION_TOTAL_PAID') ?></label>
									<span>
										<?php echo $totalPaidAmount->format() ?>
									</span>
					</li>
					<li><label><?php echo JText::_('SR_RESERVATION_COUPON_CODE') ?></label> <span><?php echo !empty($couponCode) ? $couponCode : 'N/A' ?></span></li>
				</ul>
			</div>
		</div>
	</div>
</div>

<div class="row-fluid">
	<div class="span12 reservation-detail-box">
		<h3><?php echo JText::_("SR_CUSTOMER_INFO")?></h3>
		<div class="row-fluid">
			<div class="span6">
				<ul class="reservation-details">
					<li><label><?php echo JText::_("SR_CUSTOMER_TITLE")?></label> <?php echo $this->form->getValue('customer_title') ?></li>
					<li><label><?php echo JText::_("SR_FIRSTNAME")?></label> <?php echo $this->form->getValue('customer_firstname') ?></li>
					<li><label><?php echo JText::_("SR_MIDDLENAME")?></label> <?php echo $this->form->getValue('customer_middlename') ?></li>
					<li><label><?php echo JText::_("SR_LASTNAME")?></label> <?php echo $this->form->getValue('customer_lastname') ?></li>
					<li><label><?php echo JText::_("SR_EMAIL")?></label> <?php echo $this->form->getValue('customer_email') ?></li>
					<li><label><?php echo JText::_("SR_PHONE")?></label> <?php echo $this->form->getValue('customer_phonenumber') ?></li>
					<li><label><?php echo JText::_("SR_MOBILEPHONE")?></label> <?php echo $this->form->getValue('customer_mobilephone') ?></li>
				</ul>
			</div>
			<div class="span6">
				<ul class="reservation-details">
					<li><label><?php echo JText::_("SR_COMPANY")?></label> <?php echo $this->form->getValue('customer_company') ?></li>
					<li><label><?php echo JText::_("SR_CUSTOMER_ADDRESS1")?></label> <?php echo $this->form->getValue('customer_address1') ?></li>
					<li><label><?php echo JText::_("SR_CUSTOMER_ADDRESS2")?></label> <?php echo $this->form->getValue('customer_address2') ?></li>
					<li><label><?php echo JText::_("SR_CUSTOMER_CITY")?></label> <?php echo $this->form->getValue('customer_city') ?></li>
					<li><label><?php echo JText::_("SR_CUSTOMER_ZIPCODE")?></label> <?php echo $this->form->getValue('customer_zipcode') ?></li>
					<li><label><?php echo JText::_("SR_FIELD_COUNTRY_LABEL")?></label> <?php echo $this->form->getValue('customer_country_name') ?></li>
					<li><label><?php echo JText::_("SR_VAT_NUMBER")?></label> <?php echo $this->form->getValue('customer_vat_number') ?></li>
				</ul>
			</div>
		</div>
	</div>
</div>

<div class="row-fluid">
	<div class="span12 reservation-detail-box">

		<h3><?php echo JText::_("SR_ROOM_EXTRA_INFO")?></h3>
		<table class="table">
			<thead>
			<th><?php echo JText::_("SR_ROOM_TYPE_NAME") ?></th>
			<th><?php echo JText::_("SR_ROOM_NUMBER") ?></th>
			<th><?php echo JText::_("SR_ROOM_OCCUPANCY") ?></th>
			<th><?php echo JText::_("SR_ROOM_COST") ?></th>
			<th><?php echo JText::_("SR_EXTRAS") ?></th>
			</thead>
			<tbody>
			<?php
			$reservedRoomDetails = $this->form->getValue('reserved_room_details');
			foreach($reservedRoomDetails as $room) : ?>
				<tr>
					<td><?php echo $room->room_type_name ?></td>
					<td>
						<p><?php echo $room->room_label ?></p>
						<p><?php echo JText::_("SR_GUEST_FULLNAME") ?>: <?php echo $room->guest_fullname ?></p>

						<p>
							<?php
							if (is_array($room->other_info)) :
								foreach ($room->other_info as $info) :
									if (substr($info->key, 0, 7) == 'smoking') :
										echo JText::_('SR_'.$info->key) . ': ' . ($info->value == '' ? JText::_('SR_NO_PREFERENCES') : ($info->value == 1 ? JText::_('SR_YES'): JText::_('SR_NO') )  ) ;
									endif;
								endforeach;
							endif
							?>
						</p>
					</td>
					<td>
						<p>
							<?php echo JText::_("SR_ADULT_NUMBER") ?>: <?php echo $room->adults_number ?>
						</p>
						<p>
							<?php echo JText::_("SR_CHILDREN_NUMBER") ?>: <?php echo $room->children_number ?>
						</p>
						<?php
						if (is_array($room->other_info)) :
							foreach ($room->other_info as $info) :
								echo '<ul class="unstyled">';
								if (substr($info->key, 0, 5) == 'child') :
									echo '<li>' . JText::_('SR_'.$info->key) . ': ' . $info->value .'</li>';
								endif;
								echo '</ul>';
							endforeach;
						endif;
						?>
					</td>
					<td>
						<?php
						$roomPriceCurrency = clone $baseCurrency;
						$roomPriceCurrency->setValue($room->room_price_tax_incl);
						echo $roomPriceCurrency->format()
						?>
						<span class="icon-help"
							  title="<?php echo $room->tariff_title . ' - ' . $room->tariff_description ?>">
										</span>
					</td>
					<td>
						<?php
						if (isset($room->extras)) :
						echo '<table class="table table-condensed">
													<thead>
														<th>'. JText::_("SR_RESERVATION_ROOM_EXTRA_NAME") .'</th>
														<th>'. JText::_("SR_RESERVATION_ROOM_EXTRA_QUANTITY") .'</th>
														<th>'. JText::_("SR_RESERVATION_ROOM_EXTRA_PRICE") .'</th>
													</thead>
													<tbody>
											';
						foreach($room->extras as $extra) :
						echo '<tr>';
						?>
					<td><?php echo $extra->extra_name ?></td>
					<td><?php echo $extra->extra_quantity ?></td>
					<td>
						<?php
						$extraPriceCurrency = clone $baseCurrency;
						$baseCurrency->setValue($extra->extra_price);
						echo $baseCurrency->format()
						?>
					</td>
					<?php
					echo '</tr>';
					endforeach;
					echo '
													</tbody>
												</table>';
					endif;
					?>
					</td>
				</tr>
			<?php endforeach ?>
			</tbody>
		</table>
	</div>
</div>

<div class="row-fluid">
	<div class="span12 reservation-detail-box">
		<h3><?php echo JText::_('SR_RESERVATION_OTHER_INFO') ?></h3>
		<?php
		$extras = $this->form->getValue('extras');
		if (isset($extras)) :
			echo '
						<table class="table table-condensed">
							<thead>
								<th>'. JText::_("SR_RESERVATION_ROOM_EXTRA_NAME") .'</th>
								<th>'. JText::_("SR_RESERVATION_ROOM_EXTRA_QUANTITY") .'</th>
								<th>'. JText::_("SR_RESERVATION_ROOM_EXTRA_PRICE") .'</th>
							</thead>
							<tbody>
											';
			foreach($extras as $extra) :
				echo '<tr>';
				?>
				<td><?php echo $extra->extra_name ?></td>
				<td><?php echo $extra->extra_quantity ?></td>
				<td>
					<?php
					$extraPriceCurrencyPerBooking = clone $baseCurrency;
					$extraPriceCurrencyPerBooking->setValue($extra->extra_price);
					echo $extraPriceCurrencyPerBooking->format();
					?>
				</td>
				<?php
				echo '</tr>';
			endforeach;
			echo '
							</tbody>
						</table>';
		endif;
		?>
	</div>
</div>

<div class="row-fluid">
	<div class="span12 reservation-detail-box">
		<h3><?php echo JText::_('SR_RESERVATION_NOTE_BACKEND') ?></h3>
		<div class="reservation-note-holder">
			<?php
			$notes = $this->form->getValue('notes');
			if (!empty($notes)) :
				foreach ($notes as $note) :
					?>
					<blockquote>
						<p>
							<?php echo $note->text ?>
						</p>
						<small>
							<?php echo $note->created_date ?> by <?php echo $note->username ?>
						</small>
					</blockquote>
				<?php
				endforeach;
			else :
			?>
			<div class="alert alert-info">
				<?php echo JText::_('SR_CUSTOMER_DASHBOARD_NO_NOTE') ?>
			</div>
			<?php
			endif;
			?>
		</div>
	</div>
</div>


</div>
</div>
<div class="row-fluid">
	<div class="span12 powered">
		<p>Powered by <a href="http://www.solidres.com" target="_blank">Solidres</a></p>
	</div>
</div>
</div>
<script type="text/javascript">
	Solidres.jQuery(document).ready(function($) {
		$('#item-form').validate();
		$('#solidrestoolbar-calendar button').click(function(e) {
			e.preventDefault();
			var data = {};
			data.tariff_id = 888;
			data.roomtype_id = 999999999;
			data.id = '<?php echo $this->form->getValue('reservation_asset_id') ?>';
			data.Itemid = '<?php echo $this->itemid ?>';
			data.checkin = '<?php echo $this->form->getValue('checkin') ?>';
			data.checkout = '<?php echo $this->form->getValue('checkout') ?>';
			data.reservation_id = '<?php echo $this->form->getValue('id') ?>';
			data.return = '<?php echo $this->returnPage; ?>';

			$.ajax({
				type: 'GET',
				cache: false,
				url: 'index.php?option=com_solidres&task=reservationasset.getCheckInOutFormChangeDates',
				data: data,
				success: function(response) {
					$('#changedatesform').empty().html(response);
				}
			});
		});
	});

	Joomla.submitbutton = function(task) {
		if (task == 'myreservation.cancel') {
			Joomla.submitform(task, document.getElementById('item-form'));
		}
	}
</script>
<form action="<?php JRoute::_('index.php?option=com_solidres&view=customer'); ?>" method="post" name="adminForm" id="item-form" class="">
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="return" value="<?php echo $this->returnPage; ?>" />
	<input type="hidden" name="id" value="<?php echo $this->form->getValue('id') ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>