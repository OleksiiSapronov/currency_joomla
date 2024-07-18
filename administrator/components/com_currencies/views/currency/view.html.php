<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_currencies
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('CurrenciesHelper', JPATH_COMPONENT . '/helpers/currencys.php');

/**
 * View to edit a currency.
 *
 * @since  1.5
 */
class CurrenciesViewCurrency extends JViewLegacy
{
	protected $form;

	protected $item;

	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		// Initialiase variables.
		$this->form		= $this->get('Form');
		$this->item		= $this->get('Item');
		$this->state	= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->addToolbar();
		JHtml::_('jquery.framework');
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
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user		= JFactory::getUser();
		$userId		= $user->get('id');
		$isNew		= ($this->item->id == 0);

		
			JToolbarHelper::apply('currency.apply');
			JToolbarHelper::save('currency.save');
			JToolbarHelper::cancel('currency.cancel');
			JToolbarHelper::cancel('currency.cancel', 'JTOOLBAR_CLOSE');
		

		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_COMPONENTS_CURRENCIES_EDIT');
	}
}
