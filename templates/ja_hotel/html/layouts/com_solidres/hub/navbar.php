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

<div class="navbar dashboard-nav">
	<div class="navbar-inner">
		<div class="container">
			<!-- .btn-navbar is used as the toggle for collapsed navbar content -->
			<a class="btn btn-navbar hidden-md hidden-lg" data-toggle="collapse" data-target=".nav-collapse">
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</a>
			<div class="nav-collapse">
				<ul class="nav">
					<li>
						<a href="<?php echo JRoute::_('index.php?option=com_solidres&view=dashboard') ?>">
							<?php echo JText::_('SR_DASHBOARD_NAVBAR_DASHBOARD') ?>
						</a>
					</li>
					<li class="dropdown">
						<a href="#"
						   class="dropdown-toggle"
						   data-toggle="dropdown">
							<?php echo JText::_('SR_DASHBOARD_NAVBAR_RESERVATION_ASSET') ?>
							<b class="caret"></b>
						</a>
						<ul class="dropdown-menu">
							<li>
								<a
									href="<?php echo JRoute::_('index.php?option=com_solidres&view=reservationassets') ?>">
									<?php echo JText::_('SR_DASHBOARD_MANAGE_RESERVATION_ASSET') ?>
								</a>
							</li>
							<li>
								<a
									href="<?php echo JRoute::_('index.php?option=com_solidres&view=roomtypes') ?>">
									<?php echo JText::_('SR_DASHBOARD_MANAGE_ROOM_TYPE') ?>
								</a>
							</li>
							<li>
								<a
									href="<?php echo JRoute::_('index.php?option=com_solidres&view=coupons') ?>">
									<?php echo JText::_('SR_DASHBOARD_MANAGE_COUPON') ?>
								</a>
							</li>
							<li>
								<a
									href="<?php echo JRoute::_('index.php?option=com_solidres&view=extras') ?>">
									<?php echo JText::_('SR_DASHBOARD_MANAGE_EXTRA') ?>
								</a>
							</li>
						</ul>
					</li>

					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">Reservations <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li>
								<a
									href="<?php echo JRoute::_('index.php?option=com_solidres&view=reservations') ?>">
									<?php echo JText::_('SR_DASHBOARD_MANAGE_RESERVATION') ?>
								</a>
							</li>
							<li>
								<a
									href="<?php echo JRoute::_('index.php?option=com_solidres&view=limitbookings') ?>">
									<?php echo JText::_('SR_DASHBOARD_MANAGE_LIMIT_BOOKING') ?>
								</a>
							</li>
							<!--<li>
								<a
									href="<?php /*echo JRoute::_('index.php?option=com_solidres&view=customers') */?>">
									<?php /*echo JText::_('SR_DASHBOARD_MANAGE_CUSTOMER') */?>
								</a>
							</li>-->
						</ul>
					</li>
				</ul>
			</div><!-- /.nav-collapse -->
		</div>
	</div><!-- /navbar-inner -->
</div>