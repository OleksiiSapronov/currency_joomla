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

$navBar = new JLayoutFile('hub.navbar');
if (SR_PLUGIN_STATISTICS_ENABLED) :
	SRHtml::_('jquery.chart', array('json2', 'dateAxisRenderer', 'canvasAxisLabelRenderer', 'canvasAxisTickRenderer', 'logAxisRenderer', 'canvasTextRenderer', 'highlighter', 'pieRenderer', 'cursor', 'barRenderer'));
	$document = JFactory::getDocument();
	$uncompressed = JFactory::getConfig()->get('debug') ? '' : '.min';
	$cssLink = SRURI_MEDIA.'/assets/css/';
	$jsLink = SRURI_MEDIA.'/assets/js/';
	$document->addScript($jsLink . 'jquery.scrollTo-min.js');
	$document->addStyleSheet($cssLink . 'statistics'.$uncompressed.'.css');
  $statuses = array(
		0 => JText::_('SR_RESERVATION_STATE_PENDING_ARRIVAL'),
		1 => JText::_('SR_RESERVATION_STATE_CHECKED_IN'),
		2 => JText::_('SR_RESERVATION_STATE_CHECKED_OUT'),
		3 => JText::_('SR_RESERVATION_STATE_CLOSED'),
		4 => JText::_('SR_RESERVATION_STATE_CANCELED'),
		5 => JText::_('SR_RESERVATION_STATE_CONFIRMED'),
		-2 => JText::_('JTRASHED')
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
endif;
?>
<div id="solidres">
	<?php echo $navBar->render(array('activeview' => $this->getName())) ?>

	<section id="dashboard" class="dashboard-block">
		<?php echo $this->loadTemplate('dashboard') ?>
	</section>

	<?php if (SR_PLUGIN_STATISTICS_ENABLED) : ?>
	<div  class="dashboard-block">
		<div class="row scope-selection">
			<div class="col-sm-9">
				<h3><i class="fa fa-bar-chart-o"></i> <?php echo JText::_('SR_STATISTICS_DASHBOARD') ?></h3>
			</div>
			<div class="col-sm-3">
				<select class="pull-right" id="statistics_scope" name="statistics_scope">
					<option value="0"><?php echo JText::_('JALL') ?></option>
					<?php foreach ($this->reservationAssets as $asset) : ?>
					<option <?php echo $this->statisticsScope == $asset->id ? 'selected' : '' ?> value="<?php echo $asset->id ?>"><?php echo $asset->name ?></option>
					<?php endforeach; ?>
				</select>
				<span></span>
			</div>
		</div>
		<div class="statistics-chart-area">
			<ul class="nav nav-tabs">
        <li class="active"><a href="#dashboard_statistics" data-toggle="tab"><?php echo JText::_('SR_STATISTICS_DASHBOARD_STATISTICS')?></a></li>
				<li><a href="#revenue" data-toggle="tab"><?php echo JText::_('SR_STATISTICS_REVENUE')?></a></li>
				<li><a href="#booking" data-toggle="tab"><?php echo JText::_('SR_STATISTICS_NUMBER_OF_BOOKING')?></a></li>
				<li><a href="#roomtype" data-toggle="tab"><?php echo JText::_('SR_STATISTICS_TOP_ROOM_TYPES')?></a></li>
			</ul>
			<div class="tab-content">
        <div class="tab-pane active" id="dashboard_statistics">
					<?php echo $this->loadTemplate('dashboard_statistics');?>
				</div>
				<div class="tab-pane" id="revenue">
					<?php echo $this->loadTemplate('revenue');?>
				</div>
				<div class="tab-pane" id="booking">
					<?php echo $this->loadTemplate('booking'); ?>
				</div>
				<div class="tab-pane" id="roomtype">
					<?php echo $this->loadTemplate('roomtype'); ?>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-4">
				<div class="statistics-table-area">
					<div class="statistics-box dark">
						<h4><?php echo JText::_('SR_STATISTICS_TOTAL_RESERVATION')?></h4>
						<h2>
							<?php
							if (isset($this->totalReservations)) :
								echo $this->totalReservations;
							else:
								echo JText::_('SR_STATISTICS_NO_DATA_FOUND');
							endif;
							?>
						</h2>
						<i class="fa fa-calendar"></i>
					</div>
					<div class="statistics-box dark">
						<h4><?php echo JText::_('SR_STATISTICS_LIFE_TIME_SALES')?></h4>
						<h2>
							<?php
							if (isset($this->lifeTimeSale) && isset($this->currencyCode)) :
								echo $this->lifeTimeSale . ' ' . $this->currencyCode;
							else :
								echo JText::_('SR_STATISTICS_NO_DATA_FOUND');
							endif;
							?>
						</h2>
						<i class="fa fa-money"></i>
					</div>

					<div class="statistics-box dark">
						<h4><?php echo JText::_('SR_STATISTICS_AVERAGE_BOOKING') ?></h4>
						<h2>
							<?php
							if (isset($this->average) && isset($this->currencyCode)):
								echo number_format($this->average, 2, '.','') . ' ' . $this->currencyCode;
							else:
								echo JText::_('SR_STATISTICS_NO_DATA_FOUND');
							endif;
							?>
						</h2>
						<i class="fa fa-bar-chart-o"></i>
					</div>
					<div class="statistics-box dark">
						<h4><?php echo JText::_('SR_STATISTICS_TOTAL_RESERVATION_ASSETS') ?></h4>

						<h2>
							<?php
							if (isset($this->reservationAssets)) :
								echo count($this->reservationAssets);
							else:
								echo JText::_('SR_STATISTICS_NO_DATA_FOUND');
							endif;
							?>
						</h2>
						<i class="fa fa-building-o"></i>
					</div>
					<div class="statistics-box dark">
						<h4><?php echo JText::_('SR_STATISTICS_TOTAL_ROOM_TYPES') ?></h4>
						<h2>
							<?php
							if(isset($this->totalRoomTypes)) :
								echo $this->totalRoomTypes;
							else:
								echo JText::_('SR_STATISTICS_NO_DATA_FOUND');
							endif;
							?>
						</h2>
						<i class="fa fa-home"></i>
					</div>
					<div class="statistics-box dark">
						<h4><?php echo JText::_('SR_STATISTICS_TOTAL_ROOMS') ?></h4>
						<h2>
							<?php
							if(isset($this->totalRooms)) :
								echo $this->totalRooms;
							else:
								echo JText::_('SR_STATISTICS_NO_DATA_FOUND');
							endif;
							?>
						</h2>
						<i class="fa fa-key"></i>
					</div>
				</div>
			</div>

				<div class="col-sm-8">
					<div class="">
						<h3><?php echo JText::_('SR_STATISTICS_LAST_5_BOOKING') ?></h3>
						<table class="table table-bordered">
							<thead>
							<tr>
								<th><?php echo JText::_('SR_STATISTICS_RESERVATION_CODE') ?></th>
								<th><?php echo JText::_('SR_STATISTICS_CREATED_DATE') ?></th>
								<th><?php echo JText::_('SR_STATISTICS_CUSTOMER_NAME') ?></th>
								<th><?php echo JText::_('SR_STATUS')?></th>
							</tr>
							</thead>
							<tbody>
							<?php
							for ($i = 0; $i<5; $i++) :
								if (!isset($this->latest5Booking[$i])) break;
								?>
								<tr>
									<td>
										<a href="<?php echo JRoute::_('index.php?option=com_solidres&task=reservationform.edit&id='.$this->latest5Booking[$i]->id.'&return='.base64_encode($uri))?>">
											<?php echo $this->latest5Booking[$i]->code?>
										</a>
									</td>
									<td><?php echo date('Y-m-d', strtotime($this->latest5Booking[$i]->date)) ?></td>
									<td><?php
										echo $this->latest5Booking[$i]->firstname." ";
										echo $this->latest5Booking[$i]->lastname;
										?>
									</td>
									<td width="25%">
									<span class="label <?php echo $badges[$this->latest5Booking[$i]->state]?>">
										<?php echo $statuses[$this->latest5Booking[$i]->state] ?>
									</span>

									</td>
								</tr>

							<?php endfor; ?>
							</tbody>
						</table>

					</div>
					<div class="">
						<h3><?php echo JText::_('SR_STATISTICS_WILL_CHECKIN') ?></h3>
						<table class="table table-bordered">
							<thead>
								<tr>
									<th><?php echo JText::_('SR_STATISTICS_RESERVATION_CODE') ?></th>
									<th><?php echo JText::_('SR_STATISTICS_CHECKIN_DATE') ?></th>
									<th><?php echo JText::_('SR_STATISTICS_CUSTOMER_NAME') ?></th>
									<th><?php echo JText::_('SR_STATUS')?></th>
								</tr>
							</thead>
							<tbody>
							<?php
							for ($i = 0; $i<5; $i++) :
								if (!isset($this->latest5WillCheckIn[$i])) break;
								?>
								<tr>
									<td>
										<a href="<?php	echo JRoute::_('index.php?option=com_solidres&task=reservationform.edit&id='.$this->latest5WillCheckIn[$i]->id.'&return='.base64_encode($uri)) ?>">
											<?php echo $this->latest5WillCheckIn[$i]->code ?>
										</a>
									</td>
									<td><?php echo $this->latest5WillCheckIn[$i]->checkin_date ?></td>
									<td>
										<?php
										echo $this->latest5WillCheckIn[$i]->firstname." ";
										echo $this->latest5WillCheckIn[$i]->lastname;
										?>
									</td>
									<td width="25%">
									<span class="label <?php echo $badges[$this->latest5WillCheckIn[$i]->state]?>">
										<?php echo $statuses[$this->latest5WillCheckIn[$i]->state] ?>
									</span>

									</td>
								</tr>
							<?php endfor ?>
							</tbody>
						</table>

					</div>
					<div class="">
						<h3><?php echo JText::_('SR_STATISTICS_WILL_CHECKOUT') ?></h3>
						<table class="table table-bordered">
							<thead>
							<tr>
								<th><?php echo JText::_('SR_STATISTICS_RESERVATION_CODE') ?></th>
								<th><?php echo JText::_('SR_STATISTICS_CHECKOUT_DATE') ?></th>
								<th><?php echo JText::_('SR_STATISTICS_CUSTOMER_NAME') ?></th>
								<th><?php echo JText::_('SR_STATUS')?></th>
							</tr>
							</thead>
							<tbody>
							<?php
							for ($i = 0; $i<5; $i++) :
								if (!isset($this->latest5WillCheckOut[$i])) break;
								?>
								<tr>
									<td>
										<a href="<?php echo JRoute::_('index.php?option=com_solidres&task=reservationform.edit&id='.$this->latest5WillCheckOut[$i]->id.'&return='.base64_encode($uri))?>">
											<?php echo $this->latest5WillCheckOut[$i]->code ?>
										</a>
									</td>
									<td><?php echo $this->latest5WillCheckOut[$i]->checkout_date ?></td>
									<td>
										<?php
										echo $this->latest5WillCheckOut[$i]->firstname." ";
										echo $this->latest5WillCheckOut[$i]->lastname;
										?>
									</td>
									<td width="25%">
									<span class="label <?php echo $badges[$this->latest5WillCheckOut[$i]->state]?>">
										<?php echo $statuses[$this->latest5WillCheckOut[$i]->state] ?>
									</span>

									</td>
								</tr>
							<?php endfor ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<?php if ($this->showPoweredByLink) : ?>
	<div class="row-fluid">
		<div class="span12 powered">
			<p>
				Powered by <a target="_blank" title="Solidres - A hotel booking extension for Joomla" href="http://www.solidres.com">Solidres</a>
			</p>
		</div>
	</div>
	<?php endif ?>
</div>

