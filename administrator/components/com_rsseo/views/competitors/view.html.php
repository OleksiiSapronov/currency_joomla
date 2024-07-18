<?php
/**
* @package RSSeo!
* @copyright (C) 2016 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access');

class rsseoViewCompetitors extends JViewLegacy
{	
	public function display($tpl = null) {
		$this->items 		= $this->get('Items');
		$this->pagination 	= $this->get('Pagination');
		$this->state 		= $this->get('State');
		$this->filterForm	= $this->get('FilterForm');
		$this->config 		= rsseoHelper::getConfig();
		
		$this->addToolBar();
		parent::display($tpl);
	}
	
	protected function addToolBar() {
		$parent = $this->state->get('filter.parent');
		
		$this->addScript();
		
		if (!$parent) {
			JToolBarHelper::title(JText::_('COM_RSSEO_LIST_COMPETITORS'),'rsseo');	
			JToolBarHelper::addNew('competitor.add');
			JToolBarHelper::editList('competitor.edit');
		} else {
			JToolBarHelper::title(JText::sprintf('COM_RSSEO_LIST_COMPETITORS_FOR', $this->get('competitor')),'rsseo');
			JToolBarHelper::custom('back','arrow-left','arrow-left',JText::_('COM_RSSEO_GLOBAL_BACK'),false);
		}
		
		JToolBarHelper::deleteList('COM_RSSEO_GLOBAL_CONFIRM_DELETE','competitors.delete');
		
		if (!$parent) {
			JToolBarHelper::custom('competitors.export','upload','upload_f2',JText::_('COM_RSSEO_GLOBAL_EXPORT'),false);
		}
		
		if (JFactory::getUser()->authorise('core.admin', 'com_rsseo'))
			JToolBarHelper::preferences('com_rsseo');
	}
	
	protected function addScript() {
		$scripts = array();
		
		if (!$this->config->enable_age)		$scripts[] = "jQuery('#list_fullordering option[value=\"age ASC\"], #list_fullordering option[value=\"age DESC\"]').remove();";
		if (!$this->config->enable_bingp)	$scripts[] = "jQuery('#list_fullordering option[value=\"bingp ASC\"], #list_fullordering option[value=\"bingp DESC\"]').remove();";
		if (!$this->config->enable_bingb)	$scripts[] = "jQuery('#list_fullordering option[value=\"bingb ASC\"], #list_fullordering option[value=\"bingb DESC\"]').remove();";
		if (!$this->config->enable_alexa)	$scripts[] = "jQuery('#list_fullordering option[value=\"alexa ASC\"], #list_fullordering option[value=\"alexa DESC\"]').remove();";
	
		if (!$this->config->enable_moz) {
			$scripts[] = "jQuery('#list_fullordering option[value=\"mozpagerank ASC\"]').remove();";
			$scripts[] = "jQuery('#list_fullordering option[value=\"mozpagerank DESC\"]').remove();";
			$scripts[] = "jQuery('#list_fullordering option[value=\"mozda ASC\"]').remove();";
			$scripts[] = "jQuery('#list_fullordering option[value=\"mozda DESC\"]').remove();";
			$scripts[] = "jQuery('#list_fullordering option[value=\"mozpa ASC\"]').remove();";
			$scripts[] = "jQuery('#list_fullordering option[value=\"mozpa DESC\"]').remove();";
		}
		
		if ($scripts) {
			$this->document->addScriptDeclaration('jQuery(document).ready(function() {'."\n".implode("\n", $scripts)."\n".'});');
		}
		
		$this->document->addScriptDeclaration("Joomla.submitbutton = function(task) {
			if (task == 'back') {
				jQuery('#filter_parent').val(0);
				Joomla.submitform();
				return false;
			} else {
				Joomla.submitform(task);
			}
		}");
	}
}