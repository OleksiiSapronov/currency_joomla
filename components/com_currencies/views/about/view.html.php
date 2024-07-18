<?php
defined('_JEXEC') or die;
require_once(JPATH_ROOT.'/components/com_content/helpers/route.php');
/* ini_set('display_errors',1);
ini_set('display_startup_errors',1); */
error_reporting(E_ALL);

/**

 * HTML History View class for the currencies component

 */

class CurrenciesViewAbout extends JViewLegacy
{
    public function display($tpl = null)

    {
		$app = JFactory::getApplication();
		$currencyCode = $app->input->get('code','','string');
		if($currencyCode==''){
			 throw new Exception(JText::_('COM_CURRENCIES_CURRENCY_NOT_FOUND'), 404);
            return;
		}
		$model = $this->getModel();
		$this->article = $model->getCurrencyArticle($currencyCode);
		parent::display($tpl);
	}
}