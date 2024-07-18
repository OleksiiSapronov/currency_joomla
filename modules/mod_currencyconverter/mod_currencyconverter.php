<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_custom
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once('helper.php');
$app = JFactory::getApplication();
$componentParams  = JComponentHelper::getParams('com_currencies');
$currencyFrom = $app->input->get('base','','string');
$currencyTo = $app->input->get('to','','string');
$ratesmodel = JModelLegacy::getInstance('rates','CurrenciesModel');
if($currencyFrom){
	$dataFrom = CurrencyConverterHelper::getStats($currencyFrom);
}
if($currencyTo)
{
	$dataTo = CurrencyConverterHelper::getStats($currencyTo);
}
//$oldPrice = CurrencyConverterHelper::getStats($currencyTo);
//$items = CurrencyConverterHelper::getList();
$numRows = $params->get('max_count');
$lastQueries = CurrencyConverterHelper::getConversions($params);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require JModuleHelper::getLayoutPath('mod_currencyconverter', $params->get('layout', 'default'));
