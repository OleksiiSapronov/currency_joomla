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

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
$user   = JFactory::getUser();
$userId	= $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$saveOrder = $listOrder == 'r.id';
$config = JFactory::getConfig();
$timezone = new DateTimeZone($config->get('offset'));

$statuses = array(
	0 => JText::_('SR_RESERVATION_STATE_PENDING_ARRIVAL'),
	1 => JText::_('SR_RESERVATION_STATE_CHECKED_IN'),
	2 => JText::_('SR_RESERVATION_STATE_CHECKED_OUT'),
	3 => JText::_('SR_RESERVATION_STATE_CLOSED'),
	4 => JText::_('SR_RESERVATION_STATE_CANCELED'),
	5 => JText::_('SR_RESERVATION_STATE_CONFIRMED'),
	-2 => JText::_('JTRASHED')
);

$paymentStatuses = array(
	0 => JText::_('SR_RESERVATION_PAYMENT_STATUS_UNPAID'),
	1 => JText::_('SR_RESERVATION_PAYMENT_STATUS_COMPLETED'),
	2 => JText::_('SR_RESERVATION_PAYMENT_STATUS_CANCELLED'),
	3 => JText::_('SR_RESERVATION_PAYMENT_STATUS_PENDING'),
);

$badges = array(
	0 => 'label-pending',
	1 => 'label-info',
	2 => 'label-inverse',
	3 => '',
	4 => 'label-warning',
	5 => 'label-success',
	-2 => 'label-important'
);

$uri = JUri::getInstance();
$navBar = new JLayoutFile('hub.navbar');

?>

<div id="solidres">
	<div class="row-fluid">
		<?php echo $navBar->render(array('activeview' => $this->getName())) ?>
	</div>

	<div class="row-fluid">
		<div class="span12">
			<?php echo JToolbar::getInstance('solidrestoolbar')->render('solidrestoolbar');; ?>
		</div>
	</div>

    <div class="row-fluid">
		<div id="sr_panel_right" class="sr_list_view span12">
			<form action="<?php echo JRoute::_('index.php?option=com_solidres&view=reservations'); ?>" method="post" name="adminForm" id="adminForm">
				<div class="filter-row">
					<div class="row">
						<div class="col-sm-4">
							<div class="form-group">
								<label>Filter</label>
								<input class="form-control"
							  type="text"
							  name="filter_search"
							  id="filter_search"
							  placeholder="<?php echo JText::_('SR_SEARCH_BY_CODE'); ?>"
							  value="<?php echo $this->state->get('filter.search'); ?>"
							  title="<?php echo JText::_('SR_SEARCH_BY_CODE'); ?>" />
							</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<label>Assets</label>
								<select name="filter_reservation_asset_id" class="form-control" onchange="this.form.submit()">
									<?php echo JHtml::_('select.options', SolidresHelper::getReservationAssetOptions(), 'value', 'text', $this->state->get('filter.reservation_asset_id'));?>
								</select>
							</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group">
					    	<label>Publish Status</label>
				        <select name="filter_published" class="form-control">
									<?php echo JHtml::_('select.options', $this->reservationStatusList, 'value', 'text', $this->state->get('filter.state'), true);?>
				        </select>
					    </div>
						</div>
					</div>

					<div class="form-group">
						<label>Payment</label>
						<div class="row">
							<div class="col-sm-4">
								<select name="filter_payment_status" class="inputbox form-control">
									<?php echo JHtml::_('select.options', $this->reservationPaymentStatusList, 'value', 'text', $this->state->get('filter.payment_status'), true);?>
								</select>
							</div>

							<div class="col-sm-4">
								<input type="text"
							   class="form-control"
							   name="filter_payment_method_txn_id"
							   placeholder="<?php echo JText::_('SR_RESERVATION_PAYMENT_TRANSACTION_ID') ?>"
							   value="<?php echo $this->state->get('filter.payment_method_txn_id'); ?>">
								</div>
							</div>
						</div>

					<div class="row">
						<div class="col-sm-4">
							<div class="form-group">
		            <label><?php echo JText::_('SR_RESERVATION_FILTER_FROM') ?> <i class="icon-calendar"></i> </label>
		            <input type="text" name="filter_checkin_from" id="filter_checkin_from" class="filter_checkin_checkout datepicker "
		                   value="<?php echo $this->state->get('filter.checkin_from'); ?>" />
							</div>
						</div>
				    <div class="col-sm-4">
	            <div class="form-group">
	            	<label><?php echo JText::_('SR_RESERVATION_FILTER_TO') ?> <i class="icon-calendar"></i> </label>
	            	<input type="text" name="filter_checkin_to" id="filter_checkin_to" class="filter_checkin_checkout datepicker "
	                   value="<?php echo $this->state->get('filter.checkin_to'); ?>" />
	            </div>
						</div>
					</div>
					
					<div class="row">
		        <div class="col-sm-4">
		        	<div class="form-group">
		        		<label><?php echo JText::_('SR_RESERVATION_FILTER_FROM') ?> <i class="icon-calendar"></i> </label>
		            <input type="text" name="filter_checkout_from" id="filter_checkout_from" class="filter_checkin_checkout datepicker "
		                   value="<?php echo $this->state->get('filter.checkout_from'); ?>" />
		        	</div>
		        </div>
		        <div class="col-sm-4">
		        	<div class="form-group">
		        		<label><?php echo JText::_('SR_RESERVATION_FILTER_TO') ?> <i class="icon-calendar"></i> </label>
		            <input type="text" name="filter_checkout_to" id="filter_checkout_to" class="filter_checkin_checkout datepicker "
		                   value="<?php echo $this->state->get('filter.checkout_to'); ?>" />
		        	</div>
		        </div>
					</div>

				</div>
				<button type="submit" class="btn btn-primary btn-sm"><i class="icon-search"></i> <?php echo JText::_('SR_SUBMIT') ?></button>
				<a id="reservation-filter-clear"
				   href="<?php echo JRoute::_('index.php?option=com_solidres&view=reservations&filter_clear=1') ?>"
				   class="btn btn-default"><i class="icon-remove"></i> <?php echo JText::_('JCLEAR') ?></a>
				  <hr>

				<table class="table table-striped">
					<thead>
						<tr>
							<th width="1%">
								<input type="checkbox" name="checkall-toggle" value="" onclick="Joomla.checkAll(this)" />
							</th>
							<!--<th class="nowrap">
								<?php /*echo JHtml::_('grid.sort',  'SR_HEADING_ID', 'r.id', $listDirn, $listOrder); */?>
							</th>-->
							<th>
								<?php echo JHtml::_('grid.sort',  'SR_CUSTOM_FIELD_RESERVATION_CODE', 'r.code', $listDirn, $listOrder); ?>
							</th>
							<th>
								<?php echo JHtml::_('grid.sort',  'SR_HEADING_RESERVATIONASSET', 'reservationasset', $listDirn, $listOrder); ?>
							</th>
                            <th>
								<?php echo JHtml::_('grid.sort',  'SR_RESERVATION_STATUS', 'r.state', $listDirn, $listOrder); ?>
                            </th>
							<th>
								<?php echo JText::_('SR_RESERVATION_PAYMENT_STATUS'); ?>
							</th>
							<th>
								<?php echo JHtml::_('grid.sort',  'SR_RESERVATION_CUSTOMER', 'customer_fullname', $listDirn, $listOrder); ?>
							</th>
                            <th>
								<?php echo JHtml::_('grid.sort',  'SR_RESERVATION_CHECKIN', 'r.checkin', $listDirn, $listOrder); ?>
                            </th>
                            <th>
								<?php echo JHtml::_('grid.sort',  'SR_RESERVATION_CHECKOUT', 'r.checkout', $listDirn, $listOrder); ?>
                            </th>
							<th>
								<?php echo JHtml::_('grid.sort',  'SR_CUSTOM_FIELD_RESERVATION_CREATE_DATE', 'r.created_date', $listDirn, $listOrder); ?>
							</th>
							<!--<th>
								<?php /*echo JHtml::_('grid.sort',  'SR_CUSTOM_FIELD_RESERVATION_USERNAME', 'r1.username', $listDirn, $listOrder); */?>
							</th>-->
						</tr>
		                
					</thead>
					<tfoot>
						<tr>
							<td colspan="9">
								<?php echo $this->pagination->getListFooter(); ?>
							</td>
						</tr>
					</tfoot>
					<tbody>
					<?php
						foreach ($this->items as $i => $item) :
						$ordering	= ($listOrder == 'a.ordering');
						$canCreate	= $user->authorise('core.create',       'com_solidres.reservationasset.'.$item->reservation_asset_id);
						$canEdit	= $user->authorise('core.edit',	        'com_solidres.reservationasset.'.$item->reservation_asset_id);
						//$canCheckin	= $user->authorise('core.manage',       'com_checkin') || $item->checked_out==$user->get('id') || $item->checked_out==0;
						$canChange	= $user->authorise('core.edit.state',   'com_solidres.reservationasset.'.$item->reservation_asset_id);
						$editLink	= JRoute::_('index.php?option=com_solidres&task=reservationform.edit&id='.(int) $item->id.'&return='.base64_encode($uri));
						?>
						<tr class="row<?php echo $i % 2; ?>">
							<td class="center">
								<?php echo JHtml::_('grid.id', $i, $item->id); ?>
							</td>
							<!--<td class="center">
								<?php /*echo $item->id; */?>
							</td>-->
							<td>
								<span class="label <?php echo $badges[$item->state] ?> reservation-code">
									<a href="<?php echo $editLink ?>">
										<?php echo $this->escape($item->code); ?>
									</a>
								</span>
							</td>
							<td>
								<p><?php echo $item->reservation_asset_name; ?></p>
							</td>
                            <td>
								<p><?php echo $statuses[$item->state]; ?></p>
                            </td>
							<td>
								<p><?php echo $paymentStatuses[$item->payment_status]; ?></p>
								<p><?php echo $item->payment_method_txn_id; ?></p>
							</td>
                            <td>
								<?php echo $item->customer_firstname .' '. $item->customer_middlename .' '. $item->customer_lastname ?>
							</td>
							<td>
								<?php
								echo JFactory::getDate($item->checkin, 'UTC')
								             ->setTimezone($timezone)
								             ->format('d/m/Y', true, false);
								?>
							</td>
							<td>
								<?php
								echo JFactory::getDate($item->checkout, 'UTC')
								             ->setTimezone($timezone)
								             ->format('d/m/Y', true, false);
								?>
							</td>
                            <td>
								<?php
								echo JFactory::getDate($item->created_date, 'UTC')
								             ->setTimezone($timezone)
								             ->format('d/m/Y', true, false);
								?>
                            </td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="return" value="<?php echo base64_encode(JRoute::_('index.php?option=com_solidres&view=reservationassets', false)) ?>" />
				<input type="hidden" name="boxchecked" value="0" />
                <input type="hidden" name="filter_clear" value="0" />
				<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
				<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
				<?php echo JHtml::_('form.token'); ?>
			</form>
		</div>
	</div>
	<?php if ($this->showPoweredByLink) : ?>
	<div class="row-fluid">
		<div class="span12 powered">
			<p>Powered by <a href="http://www.solidres.com" target="_blank">Solidres</a></p>
		</div>
	</div>
	<?php endif ?>
</div>
