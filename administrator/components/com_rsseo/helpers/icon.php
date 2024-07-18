<?php
/**
* @package RSSeo!
* @copyright (C) 2016 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access');

use Joomla\Utilities\ArrayHelper;

class JHtmlIcon {
	
	public static function modified($value = 0, $id) {
		// Array of image, task, title, action
		$states	= array(
			0	=> array('unpublish'),
			1	=> array('publish')
		);
		
		$state	= ArrayHelper::getValue($states, (int) $value, $states[1]);
		$icon	= $state[0];
		$html	= '<a href="javascript:void(0)" class="btn btn-micro active">';
		$html  .= '<i id="img'. $id .'" class="icon-'. $icon.'"></i>';
		$html  .= '</a>';

		return $html;
	}
	
	public static function insitemap($value = 0, $i) {
		// Array of image, task, title, action
		$states	= array(
			0	=> array('unpublish',	'pages.addsitemap',		'COM_RSSEO_PAGE_ADD_TO_SITEMAP'),
			1	=> array('publish',		'pages.removesitemap',	'COM_RSSEO_PAGE_REMOVE_FROM_SITEMAP')
		);
		
		$state	= ArrayHelper::getValue($states, (int) $value, $states[1]);
		$icon	= $state[0];
		$html	= '<a href="#" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" class="btn btn-micro ' . ($value == 1 ? 'active' : '') . '" title="'.JText::_($state[2]).'">';
		$html  .= '<i class="icon-'. $icon.'"></i>';
		$html  .= '</a>';

		return $html;
	}
}