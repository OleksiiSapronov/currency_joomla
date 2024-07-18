<?php 
defined('_JEXEC') or die;

class CurrenciesTableCurrency extends JTable{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  Database connector object
	 *
	 * @since   1.6
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__currencies_all_currencies', 'id', $db);
	}
}
