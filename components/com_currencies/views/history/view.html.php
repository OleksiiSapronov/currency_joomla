<?php
defined('_JEXEC') or die;

/* ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL); */
require_once(JPATH_ROOT.'/components/com_content/helpers/route.php');
/**

 * HTML History View class for the currencies component

 */

class CurrenciesViewHistory extends JViewLegacy
{
    public function display($tpl = null)

    {
        $app = JFactory::getApplication();
        $this->params  = JComponentHelper::getParams('com_currencies');
        $ratesModel = JModelLegacy::getInstance('rates','CurrenciesModel');
         $currencyModel =JModelLegacy::getInstance('currency','CurrenciesModel');
         $this->allCurrencies = $currencyModel->getCurrencies();
        $base = $app->input->get('base','','string');
        $currency2 = $app->input->get('to','','string');
        $value = $app->input->get('value',1,'int');
        $error = array();
        
        //see if currencies exist
        if(!$ratesModel->getCurrency($base)){
            JError::raiseError(404, JText::_($base." Base Currency ".$base." Not Found"));
            return;
        }
        
         //see if currencies exist
        if(!$ratesModel->getCurrency($currency2)){
            JError::raiseError(404, JText::_($base." Currency ".$currency2." Not Found"));
            return;
        }
        //value must be greater than 0
        if($value<1){
            JError::raiseError(404, JText::_("Value must be a positive integer"));
            return;
        }
        if(empty($error)){
        $model = $this->getModel();
        $ratesModel = JModelLegacy::getInstance('rates','CurrenciesModel');
        $this->value = $value;
        $this->baseCurrency = $currencyModel->getByCode($base);
            $this->baseCurrency->page = JRoute::_('index.php?option=com_currencies&view=pairs&base='.$this->baseCurrency->code.'&layout=landing&Itemid='.$this->params->get('pairs_itemid'));
			         
			$this->baseCurrency->article =  JRoute::_('index.php?option=com_currencies&view=about&code='.$this->baseCurrency->code);
            if($currency2){
			$this->currency2 =  $currencyModel->getByCode($currency2);
			$this->currency2->page =JRoute::_('index.php?option=com_currencies&view=pairs&base='.$this->currency2->code.'&layout=landing&Itemid='.$this->params->get('pairs_itemid'));
			$this->currency2->article =  JRoute::_('index.php?option=com_currencies&view=about&code='.$this->currency2->code);         
			}
        $this->rates = $model->getRates($this->baseCurrency->code,$this->currency2->code);;
        $this->current = $ratesModel->getCurrent($this->baseCurrency->code, $this->currency2->code);
         $this->inverse =$ratesModel->getCurrent($this->currency2->code, $this->baseCurrency->code);
         $this->percentChange = $ratesModel->getChangeRate($this->current,$this->baseCurrency->code,$this->currency2->code);
        $this->historyDays = 30;
        if(!empty($this->rates))
        $this->graph = $model->getGraphData($this->rates,$this->historyDays);
        
        $this->prepareDocument();
        }
        parent::display($tpl);
    }
    
    public function prepareDocument()
    {
        $doc = JFactory::getDocument();
		
        if(!empty($this->rates)){
        $doc->addScript('https://www.google.com/jsapi');
        $doc->addScriptDeclaration("
         google.load('visualization', '1', {packages:['corechart']});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
        ".$this->graph->data."
        ]);

        var options = {
          hAxis: {title: 'Days',  titleTextStyle: {color: '#333'},maxValue: ".$this->historyDays."},
          vAxis: {minValue: ".$this->graph->minValue."},
        };

        var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }

        ");
        }
    }
}