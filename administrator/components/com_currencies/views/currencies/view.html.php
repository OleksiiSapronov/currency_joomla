<?php 
defined('_JEXEC') or die();
require_once JPATH_COMPONENT . '/helpers/currencies.php';
class CurrenciesViewCurrencies extends JViewLegacy{
	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');

		$this->filterForm    = $this->get('FilterForm');
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		CurrenciesHelper::addSubmenu('currencies');

		$this->addToolbar();
		// Include the component HTML helpers.
		//JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);
	}
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		

		$user = JFactory::getUser();

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('COM_CURRENCIES_MANAGER_CURRENCIES'), 'bookmark currencies');
        JToolbarHelper::addNew('currencies.add');
		JToolbarHelper::editList('currencies.edit');
		JToolbarHelper::publish('currencies.publish', 'JTOOLBAR_PUBLISH', true);
		JToolbarHelper::unpublish('currencies.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		JToolbarHelper::unarchiveList('currencies.publish');
		JToolbarHelper::deleteList('', 'currencies.delete', 'JTOOLBAR_EMPTY_TRASH');
	JToolbarHelper::trash('currencies.trash');
	JToolbarHelper::preferences('com_currencies');
	

		JToolbarHelper::help('JHELP_COMPONENTS_currencies_currencies');
	}

}