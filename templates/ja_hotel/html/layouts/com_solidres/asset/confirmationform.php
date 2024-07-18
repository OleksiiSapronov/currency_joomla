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

<form
	id="sr-reservation-form-confirmation"
	class=""
	action="<?php echo JRoute::_("index.php?option=com_solidres&task=" . $displayData['task']) ?>"
	method="POST">

	<div class="row button-row button-row-top">
		<div class="col-sm-8">
			<p><?php echo JText::_("SR_RESERVATION_NOTICE_CONFIRMATION") ?></p>
		</div>
		<div class="col-sm-4 text-right">
			<div class="btn-group">
					<button type="button" class="btn reservation-navigate-back" data-step="confirmation"
							data-prevstep="guestinfo">
						<i class="icon-arrow-left uk-icon-arrow-left fa-arrow-left"></i> <?php echo JText::_('SR_BACK') ?>
					</button>
					<button disabled data-step="confirmation" type="submit" class="btn btn-success">
						<i class="icon-checkmark icon-ok uk-icon-check fa-check"></i> <?php echo JText::_('SR_BUTTON_RESERVATION_FINAL_SUBMIT') ?>
					</button>
				</div>
		</div>
	</div>
<div class="form-box">
	<div id="reservation-confirmation-box">
					<p>
						<strong><?php echo JText::_('SR_YOUR_SEARCH_INFORMATION_CHECKIN') . ' ' . JDate::getInstance($displayData['reservationDetails']->checkin, $displayData['timezone'])->format($displayData['dateFormat'], true) ?></strong>
					</p>
					<p>
						<strong><?php echo JText::_('SR_YOUR_SEARCH_INFORMATION_CHECKOUT') . ' ' . JDate::getInstance($displayData['reservationDetails']->checkout, $displayData['timezone'])->format($displayData['dateFormat'], true)  ?></strong>
					</p>

					<table class="table table-bordered">
						<tbody>
						<?php
						// Room cost
						foreach ($displayData['roomTypes'] as $roomTypeId => $roomTypeDetails) :
							foreach ($roomTypeDetails['rooms'] as $tariffId => $roomDetails) :
								foreach ($roomDetails as $roomIndex => $cost) :
									?>
									<tr>
										<td>
											<?php echo JText::_('SR_ROOM') . ': ' . $roomTypeDetails["name"] ?>
											<p><?php echo !empty($cost['currency']['title']) ? '(' . $cost['currency']['title'] . ')' : ''  ?></p>
										</td>
										<td>
											<?php
												if (0 == $displayData['booking_type']) :
													echo JText::plural("SR_NIGHTS", $displayData['stay_length']);
												else :
													echo JText::plural("SR_DAYS", $displayData['stay_length'] + 1);
												endif;
											?>
										</td>
										<td class="sr-align-right">
											<?php echo $cost['currency']['total_price_tax_excl_formatted']->format() ?>
										</td>
									</tr>
								<?php
								endforeach;
							endforeach;
						endforeach;

						// Total room cost
						$totalRoomCost = new SRCurrency($displayData['cost']['total_price_tax_excl'], $displayData['reservationDetails']->currency_id);
						?>
						<tr>
							<td colspan="2">
								<?php echo JText::_("SR_TOTAL_ROOM_COST_TAX_EXCL") ?>
							</td>
							<td class="sr-align-right">
								<?php echo $totalRoomCost->format() ?>
							</td>
						</tr>
						<?php
						// Imposed taxes
						$imposedTaxTypes = array();
						$taxId = $displayData['reservationDetails']->tax_id;
						if (!empty($taxId)) :
							$taxModel = JModelLegacy::getInstance('Tax', 'SolidresModel', array('ignore_request' => true));
							$imposedTaxTypes[] = $taxModel->getItem($taxId);
						endif;

						$totalImposedTax = 0;
						foreach ($imposedTaxTypes as $taxType) :
							$imposedAmount = $taxType->rate * $displayData['cost']['total_price_tax_excl'];
							$totalImposedTax += $imposedAmount;
							$displayData['currency']->setValue($imposedAmount);
							$taxItem = new SRCurrency($imposedAmount, $displayData['reservationDetails']->currency_id);
							?>
							<tr>
								<td colspan="2">
									<?php echo JText::_('SR_TOTAL_ROOM_TAX') ?>
								</td>
								<td class="sr-align-right">
									<?php echo $taxItem->format() ?>
								</td>
							</tr>
						<?php
						endforeach;

						// Extra cost
						$totalExtraCostTaxExcl = new SRCurrency($displayData['totalRoomTypeExtraCostTaxExcl'], $displayData['reservationDetails']->currency_id);
						$totalExtraCostTaxAmount = new SRCurrency($displayData['totalRoomTypeExtraCostTaxIncl'] - $displayData['totalRoomTypeExtraCostTaxExcl'], $displayData['reservationDetails']->currency_id);
						?>
						<tr>
							<td colspan="2">
								<?php echo JText::_("SR_TOTAL_EXTRA_COST_TAX_EXCL") ?>
							</td>
							<td id="total-extra-cost" class="sr-align-right">
								<?php echo $totalExtraCostTaxExcl->format() ?>
							</td>
						</tr>

						<tr>
							<td colspan="2">
								<?php echo JText::_("SR_TOTAL_EXTRA_COST_TAX_AMOUNT") ?>
							</td>
							<td id="total-extra-cost" class="sr-align-right">
								<?php echo $totalExtraCostTaxAmount->format() ?>
							</td>
						</tr>

						<?php
						// Grand total cost
						$grandTotal = new SRCurrency($displayData['cost']['total_price_tax_incl'] + $displayData['totalRoomTypeExtraCostTaxIncl'], $displayData['reservationDetails']->currency_id);
						?>
						<tr>
							<td colspan="2">
								<strong><?php echo JText::_("SR_GRAND_TOTAL") ?></strong>
							</td>
							<td class="sr-align-right gra">
								<strong><?php echo $grandTotal->format() ?></strong>
							</td>
						</tr>

						<?php
						// Deposit amount, if enabled
						$isDepositRequired = $displayData['reservationDetails']->deposit_required;

						if ($isDepositRequired) :
							$depositAmountTypeIsPercentage = $displayData['reservationDetails']->deposit_is_percentage;
							$depositAmount = $displayData['reservationDetails']->deposit_amount;
							$depositTotal = $depositAmount;
							if ($depositAmountTypeIsPercentage) :
								$depositTotal = ($displayData['cost']['total_price_tax_incl'] + $displayData['totalRoomTypeExtraCostTaxIncl']) * ($depositAmount / 100);
							endif;
							$depositTotalAmount = new SRCurrency($depositTotal, $displayData['reservationDetails']->currency_id);
							?>
							<tr>
								<td colspan="2">
									<strong><?php echo JText::_("SR_DEPOSIT_AMOUNT") ?></strong>
								</td>
								<td class="sr-align-right gra">
									<strong><?php echo $depositTotalAmount->format() ?></strong>
								</td>
							</tr>
							<?php
							JFactory::getApplication()->setUserState($displayData['context'] . '.deposit', array('deposit_amount' => $depositTotal));
						endif;

						// Terms and conditions
						$bookingConditionsLink = JRoute::_(ContentHelperRoute::getArticleRoute($displayData['reservationDetails']->booking_conditions));
						$privacyPolicyLink = JRoute::_(ContentHelperRoute::getArticleRoute($displayData['reservationDetails']->privacy_policy));
						?>
						<tr>
							<td colspan="3">
								<p>
									<input type="checkbox" id="termsandconditions" data-target="finalbutton"/>
									<?php echo JText::_('SR_I_AGREE_WITH') ?>
									<a target="_blank"
									   href="<?php echo $bookingConditionsLink ?>"><?php echo JText::_('SR_BOOKING_CONDITIONS') ?></a> <?php echo JText::_('SR_AND') ?>
									<a target="_blank"
									   href="<?php echo $privacyPolicyLink ?>"><?php echo JText::_('SR_PRIVACY_POLICY') ?></a>
								</p>
							</td>
						</tr>

						</tbody>
					</table>
				</div>
				<input type="hidden" name="id" value="<?php echo $displayData['assetId'] ?>"/>
</div>

	<div class="row button-row button-row-bottom">
		<div class="col-sm-8">
			<p><?php echo JText::_("SR_RESERVATION_NOTICE_CONFIRMATION") ?></p>
		</div>
		<div class="col-sm-4 text-right">
			<div class="btn-group">
				<button type="button" class="btn reservation-navigate-back" data-step="confirmation"
						data-prevstep="guestinfo">
					<i class="icon-arrow-left uk-icon-arrow-left fa fa-angle-left"></i> <?php echo JText::_('SR_BACK') ?>
				</button>
				<button disabled data-step="confirmation" type="submit" class="btn btn-success">
					<i class="icon-checkmark icon-ok uk-icon-check fa fa-check"></i> <?php echo JText::_('SR_BUTTON_RESERVATION_FINAL_SUBMIT') ?>
				</button>
			</div>
		</div>
	</div>

	<?php echo JHtml::_("form.token") ?>
</form>
