<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_currencies
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Currencies component helper.
 *
 * @since  1.6
 */
class CurrenciesHelper extends JHelperContent
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_CURRENCIES_SUBMENU_CURRENCIES'),
			'index.php?option=com_currencies&view=currencies',
			$vName == 'currencies'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_CURRENCIES_SUBMENU_COUNTRIES'),
			'index.php?option=com_currencies&view=countries',
			$vName == 'countries'
		);

	}

}
