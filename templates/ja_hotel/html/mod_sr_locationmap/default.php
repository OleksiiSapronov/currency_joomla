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
JHtml::_('stylesheet', SRURI_MEDIA.'/assets/js/colorbox/colorbox.css', false, false);
JHtml::_('script', SRURI_MEDIA.'/assets/js/colorbox/jquery.colorbox.min.js', false, false);

$staticMap = 'http://maps.googleapis.com/maps/api/staticmap?center='.$location.'&zoom=9&size=180x150&sensor=false';

?>

<?php if (!empty($location)) : ?>
<div class="solidres-module<?php echo $moduleclass_sfx ?>">
	<a
		class="show_location_map"
		href="<?php echo JRoute::_('index.php?option=com_solidres&task=map.showLocation&location='.$location) ?>"
		style="background-image: url(<?php echo $staticMap ?>); width:100%; height: 200px" >
	</a>
</div>

<?php else :
	echo JText::_('MOD_SR_LOCATIONMAP_SELECT_A_LOCATION_FIRST');
endif ?>