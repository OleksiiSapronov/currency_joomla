<?php
defined('_JEXEC') or die;
require_once(JPATH_ROOT.'/components/com_content/helpers/route.php');
$lang = JFactory::getLanguage();
$datelang = $lang->getTag();


/* ini_set('display_errors',1);
ini_set('display_startup_errors',1); */
error_reporting(E_ALL);

/**

 * HTML History View class for the currencies component

 */

 function countDecimals($fNumber)
{
	$fNumber = floatval($fNumber);
	for ($iDecimals = 0; $fNumber != round($fNumber, $iDecimals); $iDecimals++)
		;

	return $iDecimals;
}

//hi-IN Hindi
//en-CA - bengali
//en-NZ punjabi

if ($lang->getTag() == 'hi-IN' || $lang->getTag() == 'en-CA' || $lang->getTag() == 'en-NZ' || $lang->getTag() == 'en-AU') 
	
{
	
	  function decima2($value, $precision=2, $language = 'hi-EN')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}

	  function decima1($value, $precision=1, $language = 'hi-EN')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}

	  function decima0($value, $precision=0, $language = 'hi-EN')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}
	  function decima3($value, $precision=3, $language = 'hi-EN')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}

	  function decima4($value, $precision=4, $language = 'hi-EN')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}	 
 function decima5($value, $precision=5, $language = 'hi-EN')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}
	  function decima6($value, $precision=6, $language = 'hi-EN')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}
	  function decima7($value, $precision=7, $language = 'hi-EN')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}
}

elseif ($lang->getTag() == 'en-GB' || $lang->getTag() == 'th-TH'|| $lang->getTag() == 'ms-MY'|| $lang->getTag() == 'zh-TW'|| $lang->getTag() == 'he-IL'|| $lang->getTag() == 'ja-JP'|| $lang->getTag() == 'ko-KR '|| $lang->getTag() == 'zh-CN' )
{
	
		  function decima2($value, $precision=2, $language = 'en-GB')
{
    $valDecimal = new NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}

	  function decima1($value, $precision=1, $language = 'en-GB')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}

	  function decima0($value, $precision=0, $language = 'en-GB')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}
	  function decima3($value, $precision=3, $language = 'en-GB')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}

	  function decima4($value, $precision=4, $language = 'en-GB')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}	 
 function decima5($value, $precision=5, $language = 'en-GB')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}
	  function decima6($value, $precision=6, $language = 'en-GB')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}
	  function decima7($value, $precision=7, $language = 'en-GB')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}
}


elseif ($lang->getTag() == 'pt-BR')

{
	
		  function decima2($value, $precision=2, $language = 'pt-BR')
{
    $valDecimal = new NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}

	  function decima1($value, $precision=1, $language = 'pt-BR')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}

	  function decima0($value, $precision=0, $language = 'pt-BR')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}
	  function decima3($value, $precision=3, $language = 'pt-BR')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}

	  function decima4($value, $precision=4, $language = 'pt-BR')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}	 
 function decima5($value, $precision=5, $language = 'pt-BR')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}
	  function decima6($value, $precision=6, $language = 'pt-BR')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}
	  function decima7($value, $precision=7, $language = 'pt-BR')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}
}
	
else {
	
		  function decima2($value, $precision=2, $language = 'fr-FR')
{
    $valDecimal = new NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}

	  function decima1($value, $precision=1, $language = 'fr-FR')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}

	  function decima0($value, $precision=0, $language = 'fr-FR')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}
	  function decima3($value, $precision=3, $language = 'fr-FR')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}

	  function decima4($value, $precision=4, $language = 'fr-FR')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}	 
 function decima5($value, $precision=5, $language = 'fr-FR')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}
	  function decima6($value, $precision=6, $language = 'fr-FR')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}
	  function decima7($value, $precision=7, $language = 'fr-FR')
{
    $valDecimal = new \NumberFormatter($language, \NumberFormatter::DECIMAL);
    $valDecimal->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
    $valDecimal->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $precision);
    return $valDecimal->format((double) $value, \NumberFormatter::TYPE_DOUBLE);
}
}
 
class CurrenciesViewPairs extends JViewLegacy
{
	public function display($tpl = null)
	{
		$app                     = JFactory::getApplication();
		$this->params            = JComponentHelper::getParams('com_currencies');
		$ratesModel              = JModelLegacy::getInstance('rates', 'CurrenciesModel');
		$currenciesModel         = JModelLegacy::getInstance('currency', 'CurrenciesModel');
		$base                    = $app->input->get('base', '', 'string');
		$currency2               = $app->input->get('to', '', 'string');
		$value                   = $app->input->get('value', 1, 'float');
		$layout                  = $app->input->get('layout');
		$this->defaultValue      = $this->params->get('primary_value');
		$this->exchange_currency = $this->params->get('exchange_currency');
		$this->defaultCurrency   = $currenciesModel->getByCode($this->params->get('primary_currency'));
		$this->allCurrencies     = $currenciesModel->getCurrencies();
		$glang=$this->params->get('component_lang');
		
		$walutaz = $this->params->get('primary_currency');
if ($walutaz == 'PLN') {		
		
		if ($layout == 'landing' && $base == 'PLN')
		{ $this->defaultCurrency = $currenciesModel->getByCode('USD');}
		else
		{ $this->defaultCurrency = $currenciesModel->getByCode('PLN');}
				}
elseif ($walutaz == 'TRY') {		
		
		if ($layout == 'landing' && $base == 'TRY')
		{ $this->defaultCurrency = $currenciesModel->getByCode('USD');}
		else
		{ $this->defaultCurrency = $currenciesModel->getByCode('TRY');}
				}		
else {		
		
		if ($layout == 'landing' && $base == 'EUR')
		{ $this->defaultCurrency = $currenciesModel->getByCode('USD');}
		else
		{ $this->defaultCurrency = $currenciesModel->getByCode('EUR');}
				}		

		$error = array();

		$isLandingPageLayout = false;
		if ($layout == 'landing' || $layout == 'calculator')
		{
			$isLandingPageLayout = true;
		}

		//see if currencies exist
		$this->base = $currenciesModel->getByCode($base);
		if (!$this->base)
		{
			throw new Exception(JText::_($base . " Currency Not Found"), 404);

			return;
		}

		$this->currency2 = $currenciesModel->getByCode($currency2);
		//value must be greater than 0
		if ($value < 0.000001 && !$isLandingPageLayout)
		{

			$app     = JFactory::getApplication();
			$message = JText::sprintf('COM_CURRENCIES_INPUT_O_ERROR');
			$app->redirect(JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->base->code . '&to=' . $this->currency2->code . '&value=1&Itemid=' . $this->params->get('pairs_itemid')), $message, 'error');
			throw new Exception(JText::_("Value must be a positve integer"), 404);

			return;
		}

		if (!$this->currency2 && !$isLandingPageLayout)
		{
			throw new Exception(JText::_($currency2 . " Currency Not Found"), 404);

			return;
		}

		if (empty($error))
		{
			$model               = JModelLegacy::getInstance('history', 'CurrenciesModel');
			$this->value         = $value;
			$this->base->page    = JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->base->code . '&layout=landing&Itemid=' . $this->params->get('pairs_itemid'));
			$this->base->article = JRoute::_('index.php?option=com_currencies&view=about&code=' . $this->base->code);
			$this->historyDays   = $this->params->get('history_days_limit');
			if ($this->currency2)
			{
				$this->currency2->page    = JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->currency2->code . '&layout=landing&Itemid=' . $this->params->get('pairs_itemid'));
				$this->currency2->article = JRoute::_('index.php?option=com_currencies&view=about&code=' . $this->currency2->code);
				$cfg                      = array('limit' => $this->params->get('history_rates_list_limit', 30));
//            $this->rates = $ratesModel->getHistory($this->base->code,$this->currency2->code,$cfg);
				$this->rates   = $ratesModel->getExchangeHistory($this->base->code, $this->currency2->code, $cfg);
				$this->current = $ratesModel->getCurrent($this->base->code, $this->currency2->code);
				//record conversion
				if ($value > 0)
					//$ratesModel->recordConversion(array('from'=>$this->base->code,'to'=>$this->currency2->code,'amount'=>$value));
					if (!empty($this->rates))
						$this->graph = $ratesModel->getGraphData($this->rates, $this->historyDays);
			}

			if ($layout == 'landing')
			{
				$this->current = $ratesModel->getCurrent($this->base->code, $this->defaultCurrency->code);
				$this->inverse = $ratesModel->getCurrent($this->defaultCurrency->code, $this->base->code);
			}


			if (!$isLandingPageLayout)
			{

				$this->inverse       = $ratesModel->getCurrent($this->currency2->code, $this->base->code);
				$this->percentChange = $ratesModel->getChangeRate($this->current, $this->base->code, $this->currency2->code);


				$this->historyDays = $this->params->get('history_days_limit');

			}

		}

		if ($isLandingPageLayout)
		{
			$this->ratesModel      = $ratesModel;
			$this->model           = $model;
			$landingPageCurrencies = $this->params->get('landing_page_currencies');

			$this->baseRate = $this->ratesModel->convert('USD', $this->base->code);

			if ($landingPageCurrencies && strlen($landingPageCurrencies) > 0)
			{
				$landingPageCurrencies = explode(',', $landingPageCurrencies);
				foreach ($landingPageCurrencies as $key => $value)
				{
					if ($value == $this->base->code)
					{
						$db  = JFactory::getDbo();
						$sql = 'select code from #__currencies_all_currencies where code<>' . $db->quote($value) . ' limit 1';
						$db->setQuery($sql);
						$result                      = $db->loadObject();
						$landingPageCurrencies[$key] = $result->code;
					}
				}
			}
			else
			{
				throw new Exception(JText::_('COM_CURRENCIES_LANDING_PAGE_CURRENCIES_NOT_SET'), 404);

			}

			/*$rates = $this->ratesModel->getCurrentBunch($this->base->code, $landingPageCurrencies);
			$this->rates  = array();
			foreach($rates['results']['rate'] as $rate){
				$parts = explode('/',$rate['Name']);
				$this->rates[$parts[1]] = $rate;
			}*/

			$rates = $this->ratesModel->getCurrentRatesBunch($this->base->code, $landingPageCurrencies);
			foreach ($rates as $rate)
			{
				$this->rates[$rate->currency2] = $rate->rate;
			}
			/*$rates_reverse = $this->ratesModel->getCurrentBunch_reverse($this->base->code, $landingPageCurrencies);
                        
                       
			$this->rates_reverse  = array();
			foreach($rates_reverse['results']['rate'] as $rate){
                           
				$parts = explode('/',$rate['Name']);
                                
				$this->rates_reverse[$parts[0]] = $rate;
			}*/
			$this->currencies = $currenciesModel->getCurrenciesByCodes($landingPageCurrencies);
			//$this->currencies = $currenciesModel->getLandingPageCurrencies($landingPageCurrencies);


			$this->historyRates = array();
			$cfg                = array('group_by' => 'currency2',
			                            'limit'    => $this->params->get('landing_page_rates_list_limit')
			);
			$this->historyRates = $this->ratesModel->getHistory($this->base->code, null, $cfg, $this->exchange_currency);

			/* echo "<pre>";
			print_r($this->historyRates);
			exit; */
//			$this->prepareDocument();
			$this->setLayout($layout);
		}
		$this->otherCurrencies = $ratesModel->getOtherCurrencies();
		$this->prepareDocument();
		parent::display($tpl);
	}

    public function prepareDocument()
    {
		$app = JFactory::getApplication();
        $doc = JFactory::getDocument();
		$layout = $app->input->get('layout');
		JHtml::_('bootstrap.framework');
		JHTML::_('behavior.formvalidator');
		//JHtml::_('formbehavior.chosen', 'select');
		$doc->addScript(JUri::root().'components/com_currencies/assets/js/jquery.ddslick.min.js');
		$doc->addScriptDeclaration("
		jQuery(document).ready(function(){
		jQuery('#currencyselect_to, #currencyselect_from').ddslick({
			width:100
		});	
		})
		  
		");
//		$doc->addScript(JUri::root().'components/com_currencies/assets/js/jquery.ddslick.min.js');
		$doc->addScript(JUri::root().'components/com_currencies/assets/js/raphael-min.js');
		$doc->addScript(JUri::root().'components/com_currencies/assets/js/morris.js');
        if(!empty($this->rates) && isset($this->graph)){

           
            $minValue = $this->graph->minValue;
            $maxValue = $this->graph->maxValue;
            $minValue = $minValue - ($minValue * 0.01);
            $maxValue = $maxValue + ($maxValue * 0.01);
            
            $doc->addScriptDeclaration(" 
                jQuery(function () {
                            var d = new Date();
                            var month = new Array();
                            month[0] = 'January';
                            month[1] = 'February';
                            month[2] = 'March';
                            month[3] = 'April';
                            month[4] = 'May';
                            month[5] = 'June';
                            month[6] = 'July';
                            month[7] = 'August';
                            month[8] = 'September';
                            month[9] = 'October';
                            month[10] = 'November';
                            month[11] = 'December';
                            var n = month[d.getMonth()];                


                Morris.Area({
                    element: 'graph',
                    data: [
                      ".$this->graph->data."
                    ],
                    yLabelFormat: function (y) { y = parseFloat(y);  if(y>1){ return y.toFixed(3); }else{ return y.toFixed(6); } },
                    xkey: 'x',
                    ykeys: ['y','y'],
                    labels: ['Rate','1 ".$this->base->code." in ".$this->currency2->code."'], 
                    ymin:".$minValue.",
                    ymax:".$maxValue.",
                    xLabelFormat: function(d) { 
                    return month[d.getMonth()]+', '+d.getDate(); 
                    }
                  });
              });
              " );
        }
    }
}