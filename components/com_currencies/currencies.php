<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_currencies
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$doc = JFactory::getDocument();
$doc->addStyleSheet(Juri::root().'components/com_currencies/assets/css/style.css');
//$doc->addStyleSheet('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css');
require_once(JPATH_COMPONENT.'/helpers/currencies.php');
JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_currencies/models');
$currencyModel = JModelLegacy::getInstance('currency','CurrenciesModel');
$currencyModel->getAllCurrencies();
$controller = JControllerLegacy::getInstance('Currencies');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
