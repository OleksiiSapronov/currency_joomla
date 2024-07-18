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

$uri = JUri::getInstance($displayData['uri']);
// This is needed to make the filter module play nice with the menu's request variable.
// If the url contains 'view=*' then we have to remove it and append task variable for the ajax filter
// to work properly
if (!empty($displayData['view'])) :
	$uri->delVar('view'); // When SEF is enabled, variable 'view' does not exists
	$uri->setVar('task', 'hub.search');
endif;
$inactiveCSSClass = 'icon-checkbox-unchecked icon-check-empty uk-icon-square-o fa-square-o';
$activeCSSClass = 'icon-checkbox icon-check uk-icon-check-square-o fa-check-square-o';
$context = 'com_solidres.reservation.process';
$currencyCode = JFactory::getApplication()->getUserState($context.'.currency_code');
?>
<form id="sr-filter-form" action="<?php echo JRoute::_('index.php', false) ?>" method="GET" class="form-stacked sr-validate">
	<fieldset>
				<legend><?php echo JText::_('SR_FILTER_BY_PRICE') ?></legend>
				<ul>
					<?php

					if (!empty($displayData['searchConditions']['prices']) ) :
						$rangeTopArr = explode('-', $displayData['searchConditions']['prices'][0]);
						$rangeTopTotal = $rangeTopArr[0] + ($rangeTopArr[1] != 'plus' ? $rangeTopArr[1] : '9999999999') ;
						$rangeBottomArr = explode('-', $displayData['searchConditions']['prices'][count($displayData['searchConditions']['prices']) - 1]);
						$rangeBottomTotal = $rangeBottomArr[0] + ($rangeBottomArr[1] != 'plus' ? $rangeTopArr[1] : '9999999999') ;
					endif;

					foreach ($displayData['filters']['prices'] as $range) :

						if (empty($displayData['searchConditions']['prices'])) :
							$uri->setVar('prices', $range);
						else :
							$uri->setVar('prices', implode(',', $displayData['searchConditions']['prices']));
						endif;

						if (!empty($displayData['searchConditions']['stars'])) :
							$uri->setVar('stars', implode(',', $displayData['searchConditions']['stars']));
						else :
							$uri->delVar('stars');
						endif;

						if (!empty($displayData['searchConditions']['categories'])) :
							$uri->setVar('categories', implode(',', $displayData['searchConditions']['categories']));
						else :
							$uri->delVar('categories');
						endif;

						if (!empty($displayData['searchConditions']['facilities'])) :
							$uri->setVar('facilities', implode(',', $displayData['searchConditions']['facilities']));
						else :
							$uri->delVar('facilities');
						endif;

						if (!empty($displayData['searchConditions']['themes'])) :
							$uri->setVar('themes', implode(',', $displayData['searchConditions']['themes']));
						else :
							$uri->delVar('themes');
						endif;

						// Attemp to specify whether this range is active or not
						// For example we have the following ranges
						// 0 - 100
						// 101 - 200
						// 201 - 300
						// 301 - 400
						// If range 0 - 100 and 301 - 400 are selected, all ranges in between are selected too
						$isActive = false;
						$isActiveInBetween = false;
						if (!empty($displayData['searchConditions']['prices']) ) :
							$rangeArr = explode('-', $range);
							$rangeTotal = $rangeArr[0] + ($rangeArr[1] != 'plus' ? $rangeArr[1] : '9999999999') ;

							if (in_array($range, (array) $displayData['searchConditions']['prices'])) :
								$isActive = true;
							endif;

							if ($rangeTotal > $rangeTopTotal && $rangeTotal < $rangeBottomTotal) :
								$isActiveInBetween = true;
							endif;
						endif;

						$tmp = explode('-', $range);

						echo '<li>
							<a data-filteringkey="prices"
								data-filteringvalue="' . $range . '"
								class="triggerfiltering '.($isActive ? 'active' : '').' '.($isActiveInBetween ? 'nouncheck' : '' ).' "
								' . ($isActiveInBetween ? '' : 'href="' .$uri->toString()) . '">
							<i class="'.($isActive || $isActiveInBetween ? $activeCSSClass : $inactiveCSSClass ).'"></i>
							' . $currencyCode .' '. $tmp[0] . ($tmp[1] != 'plus' ? ' - ' . $currencyCode .' '. $tmp[1] : '+') .'</a></li>';
					endforeach ?>

				</ul>
			</fieldset>
			<fieldset>
				<legend><?php echo JText::_('SR_FILTER_BY_STAR_RATING') ?></legend>
				<ul>
					<?php
					foreach ($displayData['filters']['stars'] as $star => $filterCount) :
						if ($filterCount > 0) :
							if (empty($displayData['searchConditions']['stars'])) :
								$uri->setVar('stars', $star);
							else :
								$uri->setVar('stars', implode(',', $displayData['searchConditions']['stars']));
							endif;

							if (!empty($displayData['searchConditions']['prices'])) :
								$uri->setVar('prices', implode(',', $displayData['searchConditions']['prices']));
							else :
								$uri->delVar('prices');
							endif;

							if (!empty($displayData['searchConditions']['categories'])) :
								$uri->setVar('categories', implode(',', $displayData['searchConditions']['categories']));
							else :

								$uri->delVar('categories');
							endif;

							if (!empty($displayData['searchConditions']['facilities'])) :
								$uri->setVar('facilities', implode(',', $displayData['searchConditions']['facilities']));
							else :
								$uri->delVar('facilities');
							endif;

							if (!empty($displayData['searchConditions']['themes'])) :
								$uri->setVar('themes', implode(',', $displayData['searchConditions']['themes']));
							else :
								$uri->delVar('themes');
							endif;

							$isActive = in_array($star, (array) $displayData['searchConditions']['stars']);

							echo '<li>
							<a data-filteringkey="stars"
								data-filteringvalue="' . $star . '"
								class="triggerfiltering '.($isActive ? 'active' : '').'"
								href="' . $uri->toString() . '">
							<i class="'.($isActive ? $activeCSSClass : $inactiveCSSClass ).'"></i>
							' . $star . ' ' . ($star == 1 ? JText::_('SR_STAR') : JText::_('SR_STARS')) . ' (' . $filterCount . ')</a></li>';
						endif;
					endforeach
					?>
				</ul>
			</fieldset>
			<fieldset>
				<legend><?php echo JText::_('SR_FILTER_BY_CATEGORY') ?></legend>
				<ul>
					<?php
					foreach ($displayData['filters']['categories'] as $category) :

						if (!empty($displayData['searchConditions']['prices'])) :
							$uri->setVar('prices', implode(',', $displayData['searchConditions']['prices']));
						else :
							$uri->delVar('prices');
						endif;

						if (!empty($displayData['searchConditions']['stars'])) :
							$uri->setVar('stars', implode(',', $displayData['searchConditions']['stars']));
						else :
							$uri->delVar('stars');
						endif;

						if (empty($displayData['searchConditions']['categories'])) :
							$uri->setVar('categories', $category->id);
						else :
							$uri->setVar('categories', implode(',', $displayData['searchConditions']['categories']));
						endif;

						if (!empty($displayData['searchConditions']['facilities'])) :
							$uri->setVar('facilities', implode(',', $displayData['searchConditions']['facilities']));
						else :
							$uri->delVar('facilities');
						endif;

						if (!empty($displayData['searchConditions']['themes'])) :
							$uri->setVar('themes', implode(',', $displayData['searchConditions']['themes']));
						else :
							$uri->delVar('themes');
						endif;

						$isActive = in_array($category->id, (array) $displayData['searchConditions']['categories']);

						echo '<li>
						<a data-filteringkey="categories"
							data-filteringvalue="' . $category->id . '"
							class="triggerfiltering '.($isActive ? 'active' : '').'"
							href="' . $uri->toString() . '">
						<i class="'.(in_array($category->id, (array) $displayData['searchConditions']['categories']) ? $activeCSSClass : $inactiveCSSClass ).'"></i>
						' . $category->title . ' ' . ' (' . $category->count . ')</a></li>';
					endforeach
					?>
				</ul>
			</fieldset>
			<fieldset>
				<legend><?php echo JText::_('SR_FILTER_BY_FACILITY') ?></legend>
				<ul>
					<?php
					foreach ($displayData['filters']['facilities'] as $facility) :
						if (!empty($displayData['searchConditions']['prices'])) :
							$uri->setVar('prices', implode(',', $displayData['searchConditions']['prices']));
						else :
							$uri->delVar('prices');
						endif;

						if (!empty($displayData['searchConditions']['stars'])) :
							$uri->setVar('stars', implode(',', $displayData['searchConditions']['stars']));
						else :
							$uri->delVar('stars');
						endif;

						if (!empty($displayData['searchConditions']['categories'])) :
							$uri->setVar('categories', implode(',', $displayData['searchConditions']['categories']));
						else :
							$uri->delVar('categories');
						endif;

						if (empty($displayData['searchConditions']['facilities'])) :
							$uri->setVar('facilities', $facility->id);
						else :
							$uri->setVar('facilities', implode(',', $displayData['searchConditions']['facilities']));
						endif;

						if (!empty($displayData['searchConditions']['themes'])) :
							$uri->setVar('themes', implode(',', $displayData['searchConditions']['themes']));
						else :
							$uri->delVar('themes');
						endif;

						$isActive = in_array($facility->id, (array) $displayData['searchConditions']['facilities']);
						echo '<li>
						<a data-filteringkey="facilities"
							data-filteringvalue="' . $facility->id . '"
							class="triggerfiltering '.($isActive ? 'active' : '').'"
							href="' . $uri->toString() . '">
						<i class="'.(in_array($facility->id, (array) $displayData['searchConditions']['facilities']) ? $activeCSSClass : $inactiveCSSClass ).'"></i>
						' . $facility->title . ' ' . ' (' . $facility->count . ')</a></li>';
					endforeach
					?>
				</ul>
			</fieldset>
			<fieldset>
				<legend><?php echo JText::_('SR_FILTER_BY_THEME') ?></legend>
				<ul>
					<?php
					foreach ($displayData['filters']['themes'] as $theme) :
						if (!empty($displayData['searchConditions']['prices'])) :
							$uri->setVar('prices', implode(',', $displayData['searchConditions']['prices']));
						else :
							$uri->delVar('prices');
						endif;

						if (!empty($displayData['searchConditions']['stars'])) :
							$uri->setVar('stars', implode(',', $displayData['searchConditions']['stars']));
						else :
							$uri->delVar('stars');
						endif;

						if (!empty($displayData['searchConditions']['categories'])) :
							$uri->setVar('categories', implode(',', $displayData['searchConditions']['categories']));
						else :
							$uri->delVar('categories');
						endif;

						if (!empty($displayData['searchConditions']['facilities'])) :
							$uri->setVar('facilities', implode(',', $displayData['searchConditions']['facilities']));
						else :
							$uri->delVar('facilities');
						endif;

						if (empty($displayData['searchConditions']['themes'])) :
							$uri->setVar('themes', $theme->id);
						else :
							$uri->setVar('themes', implode(',', $displayData['searchConditions']['themes']));
						endif;

						$isActive = in_array($theme->id, (array) $displayData['searchConditions']['themes']);
						echo '<li>
						<a data-filteringkey="themes"
							data-filteringvalue="' . $theme->id . '"
							class="triggerfiltering '.($isActive ? 'active' : '').'"
							href="' . $uri->toString() . '">
						<i class="'.(in_array($theme->id, (array) $displayData['searchConditions']['themes']) ? $activeCSSClass : $inactiveCSSClass ).'"></i>
						' . $theme->title . ' ' . ' (' . $theme->count . ')</a></li>';
					endforeach
					?>
				</ul>
			</fieldset>

	<input type="hidden" name="option" value="com_solidres"/>
	<input type="hidden" name="task" value="hub.search"/>
	<input type="hidden" name="Itemid" value="<?php echo $displayData['params']->get('target_itemid') ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>