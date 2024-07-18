<?php 
defined('_JEXEC') or die;

class TableCountry extends JTable{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  Database connector object
	 *
	 * @since   1.6
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__countries', 'id', $db);
	}
}
