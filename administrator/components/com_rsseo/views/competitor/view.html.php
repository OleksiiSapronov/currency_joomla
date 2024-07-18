<?php
/**
* @package RSSeo!
* @copyright (C) 2016 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access');

class rsseoViewCompetitor extends JViewLegacy
{	
	public function display($tpl = null) {
		$this->form 		 = $this->get('Form');
		$this->item 		 = $this->get('Item');
		
		$this->addToolbar();
		parent::display($tpl);
	}
	
	protected function addToolbar() {
		JToolBarHelper::title(JText::_('COM_RSSEO_COMPETITOR_EDIT'),'rsseo');
				
		JToolBarHelper::apply('competitor.apply');
		JToolBarHelper::save('competitor.save');
		JToolBarHelper::cancel('competitor.cancel');
	}
}