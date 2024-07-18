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

<div class="navbar search-navbar">
	<div class="navbar-inner">
		<a class="btn btn-navbar hidden-md hidden-lg" data-toggle="collapse" data-target=".navbar-responsive-collapse">
			<i class="fa fa-bars"></i>
		</a>
		<div class="nav-collapse collapse navbar-responsive-collapse">
			<ul class="nav">
				<li class=""><a href="<?php echo $this->uri->toString() ?>"><?php echo JText::_('SR_NAVBAR_RECOMMENDED') ?></a></li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo JText::_('SR_NAVBAR_SORTBY_STAR') ?> <b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a data-sortingkey="star" data-sortingvalue="asc" class="triggersorting" href="<?php echo $this->sortByStars['asc'] ?>"><?php echo JText::_('SR_NAVBAR_SORTBY_STAR_ASC') ?></a></li>
						<li><a data-sortingkey="star" data-sortingvalue="desc" class="triggersorting" href="<?php echo $this->sortByStars['desc'] ?>"><?php echo JText::_('SR_NAVBAR_SORTBY_STAR_DESC') ?></a></li>
					</ul>
				</li>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo JText::_('SR_NAVBAR_SORTBY_NAME') ?> <b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li><a data-sortingkey="name" data-sortingvalue="asc" class="triggersorting" href="<?php echo $this->sortByName['asc'] ?>"><?php echo JText::_('SR_NAVBAR_SORTBY_NAME_ASC') ?></a></li>
						<li><a data-sortingkey="name" data-sortingvalue="desc"class="triggersorting" href="<?php echo $this->sortByName['desc'] ?>"><?php echo JText::_('SR_NAVBAR_SORTBY_NAME_DESC') ?></a></li>
					</ul>
				</li>
			</ul>
			<ul class="nav pull-right">
				<li><a data-sortingkey="mode" data-sortingvalue="list" class="triggersorting" href="<?php echo $this->sortByMode['list'] ?>"><i class="icon-th-list uk-icon-th-list icon-list-view"></i> <?php echo JText::_('SR_NAVBAR_LIST') ?> </a></li>
				<li><a data-sortingkey="mode" data-sortingvalue="grid" class="triggersorting" href="<?php echo $this->sortByMode['grid'] ?>"><i class="icon-th-large uk-icon-th-large icon-grid-view"></i> <?php echo JText::_('SR_NAVBAR_GRID') ?> </a></li>
			</ul>
		</div>
	</div><!-- /navbar-inner -->
</div>