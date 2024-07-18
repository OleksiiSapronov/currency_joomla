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
 * Currencies list controller class.
 *
 * @since  1.6
 */
class CurrenciesControllerCurrencies extends JControllerAdmin
{
	/**
	 * @var     string  The prefix to use with controller messages.
	 *
	 * @since   1.6
	 */
	protected $text_prefix = 'com_currencies_currencies';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

	}
    public function add(){
		$app= JFactory::getApplication();
		$app->redirect('index.php?option=com_currencies&view=currency&layout=edit');
	}
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Currency', $prefix = 'CurrenciesModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	
}
