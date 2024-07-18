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
//$solidresConfig = JComponentHelper::getParams('com_solidres');
/*$searchConditions = $app->getUserState($context.'.search_conditions');
$filterLayout = new JLayoutFile('hub.filter');
$displayData = array(
	'filters' => array(
		'prices' => $filterByPrice,
		'stars' => $filterByStar,
		'categories' => $filterByCategory,
		'facilities' => $filterByFacility,
		'themes' => $filterByTheme
	),
	'params' => $params,
	'searchConditions' => $searchConditions,
	'uri' => $uri,
	'view' => $view,
	'show_price_filter' => $showPriceFilter,
	'show_rating_filter' => $showRatingFilter,
	'show_category_filter' => $showCategoryFilter,
	'show_facility_filter' => $showFacilityFilter,
	'show_theme_filter' => $showThemeFilter
);*/
?>
<?php /*if (!empty($searchConditions['city'])) : */?>
<div class="solidres-module-filter" id="solidres-module-filter">
	<?php //echo $filterLayout->render($displayData); ?>
</div>
<?php /*else :
	echo JText::_('SR_FILTER_SELECT_A_LOCATION_FIRST');
endif; */?>

