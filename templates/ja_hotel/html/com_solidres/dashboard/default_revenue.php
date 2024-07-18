<?php
/*------------------------------------------------------------------------
  Solidres - Hotel booking extension for Joomla
  ------------------------------------------------------------------------
  @Author    Solidres Team
  @Website   http://www.solidres.com
  @Copyright Copyright (C) 2013 - 2014 Solidres. All Rights Reserved.
  @License   GNU General Public License version 3, or later
------------------------------------------------------------------------*/
/**
 * Statistics revenue view
 *
 * @package     Solidres
 * @subpackage	Statistics
 * @since		0.5.0
 */
defined('_JEXEC') or die;

?>
<div class="navbar dashboard-nav">
	<div class="navbar-inner">
			<!-- .btn-navbar is used as the toggle for collapsed navbar content -->
			<a class="btn btn-navbar hidden-md hidden-lg" data-toggle="collapse" data-target=".nav-collapse">
				<i class="fa fa-bars"></i>
			</a>
			<!-- Be sure to leave the brand out there if you want it shown -->
			<!--<a class="brand" href="#">Project name</a>-->
			<!-- Everything you want hidden at 940px or less, place within here -->
			<div class="nav-collapse collapse">

				<ul class="nav">
					<li>
						<a data-range="today"  href=""><?php echo JText::_('SR_STATISTICS_TO_DAY') ?></a>
					</li>
					<li>
						<a data-range="thisweek" href=""><?php echo JText::_('SR_STATISTICS_THIS_WEEK') ?></a>
					</li>
					<li>
						<a data-range="thismonth" href=""><?php echo JText::_('SR_STATISTICS_THIS_MONTH') ?></a>
					</li>
					<li>
						<a data-range="last3" href=""><?php echo JText::_('SR_STATISTICS_LAST_3_MONTH') ?></a>
					</li>
					<li>
						<a data-range="last6" href=""><?php echo JText::_('SR_STATISTICS_LAST_6_MONTH') ?></a>
					</li>
					<li>
						<a data-range="lastweek" href=""><?php echo JText::_('SR_STATISTICS_LAST_WEEK') ?></a>
					</li>
					<li>
						<a data-range="lastmonth" href=""><?php echo JText::_('SR_STATISTICS_LAST_MONTH') ?></a>
					</li>
					<li>
						<a data-range="lastyear" href=""><?php echo JText::_('SR_STATISTICS_LAST_YEAR') ?></a>

					</li>
				</ul>

				<ul class="nav pull-right">
					<li>
						<a data-range="customrange" href="#">
							<i class="icon-wrench"></i>
							<?php echo JText::_('SR_STATISTICS_AD_OPTIONS') ?>
						</a>

					</li>
					<div class="date-toggle">
						<div class="date-customtab">
							<li><?php echo JText::_('SR_STATISTICS_CUSTOMRANGE') ?></li>
							<input class="customFrom" type="text"  readonly="true" placeholder=<?php echo JText::_('SR_STATISTICS_FROM')?> >
							<input class="customTo" type="text"   readonly="true" placeholder=<?php echo  JText::_('SR_STATISTICS_TO')?> >
							<button class="date-submit1" type="button" class="btn"><?php echo JText::_('SR_STATISTICS_APPLY') ?></button>
							<hr/>
							<?php echo JText::_('SR_STATISTICS_COMPARE_TO') ?>
							<select id="CR" class="compare" data-compare-type="revenue">
								<option value="Se">Please select</option>
								<option value="Pre">Previous period</option>
								<option value="Cus">Custom</option>
							</select>

							<input id="inCRFrom" class="disabledInputFrom" type="text"  readonly="true" placeholder=<?php echo JText::_('SR_STATISTICS_FROM')?> >
							<input id="inCRTo" class="disabledInputTo" type="text"  readonly="true" placeholder=<?php echo  JText::_('SR_STATISTICS_TO')?> >
							<button class="date-submit2" disabled type="button" class="btn"><?php echo JText::_('SR_STATISTICS_APPLY') ?></button>
						</div>
					</div>
				</ul>

				</ul>
				<!-- .nav, .navbar-search, .navbar-form, etc -->
			</div>

		</div>
</div>
<div id="chart1"></div>