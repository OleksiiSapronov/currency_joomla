<?php

/**
 * @package        com_currencies
 * @author        Tom Mwenda
 * @email       tommwenda@gmail.com
 * @license        GPL2
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');
//enable errors on a single page
/*ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);*/
class CurrenciesControllerRates extends JControllerLegacy
{
    public function convert_ws()
    {
        $app = JFactory::getApplication();
        $model = JModelLegacy::getInstance('rates','CurrenciesModel');
        $base = $app->input->get('base','','string');
        $to = $app->input->get('currency2','','string');
        $amount = $app->input->get('amount');
        $error = array();
        $response = array();
        $data = array();
        $data['from'] = $base;
        $data['to'] = $to;
        $data['amount'] = $amount;
        $model->recordConversion($data);
        if(strlen($base)<3){
            $error[]='Base currency too short';
        }

        if(strlen($to)<3){
            $error[]='Second currency too short';
        }
        
        $rate = $model->convert($base, $to);
        $value = ($rate[1]*$amount);
		$value = number_format($value,3);
        echo $value;
        exit;
    }
    public function convert()
	{
		$model = JModelLegacy::getInstance('rates','CurrenciesModel');
		$app = JFactory::getApplication();
		$value = $app->input->get('value');
		$base = $app->input->get('base');
		$to = $app->input->get('to');
		$data = array();
        $data['from'] = $base;
        $data['to'] = $to;
        $data['amount'] = $value;
		 $model->recordConversion($data);
		$url = JRoute::_('index.php?option=com_currencies&view=pairs&base='.$base.'&to='.$to.'&value='.$value);
		$app->redirect($url);
	}
	public function updateRates()
	{
		echo 'This service is currently unavailable';
		exit;
	}
    public function test(){
		 $model = JModelLegacy::getInstance('currency','CurrenciesModel');
		 $ratesmodel = JModelLegacy::getInstance('rates','CurrenciesModel');
		 $params  = JComponentHelper::getParams('com_currencies');
		 $featured = $params->get('featured_currencies');
		 $featured = explode(',', $featured);		
		 $result = $ratesmodel->getCurrentBunch('KES', $featured);
		 $ratesmodel->updateRates($result['results']['rate']);
	}
	
	 /* Method to update rates for all the currency pairs, developed to be used by cron */
	 /*public function updateAllRates(){
		 $time_start = microtime(true);
		 
		 $model = JModelLegacy::getInstance('currency','CurrenciesModel');
		 $ratesmodel = JModelLegacy::getInstance('rates','CurrenciesModel');
		 
		 $allCurrencies = $model->getCurrencies();
		 $allCurrencies =  array_map(function($e) { return is_object($e) ? $e->code : $e['code'];}, $allCurrencies);
		 $currencies = implode(",", $allCurrencies);
		 
		 foreach($allCurrencies as $base)
		 {	
			$resultData = $ratesmodel->getCurrentRates($base, $currencies);
			
			$recordArray = array();
			if(!empty($resultData['success']))
			{
				foreach($resultData['quotes'] as $key => $val)
				 {				 
					$currency = substr($key, 3, 3);
					
					if($base != $currency)
					{	
						$resultArray[0] = $resultData['timestamp'];
						$resultArray[1] = $val; 
						$pair = $base.'/'.$currency;
						$ratesmodel->updateCurrentRates($pair,$resultArray);	
						
						$rArray['pair'] = $base.$currency;
						$rArray['rate'] = $val;
						$rArray['date'] = date("m/d/Y", $resultData['timestamp']);
						$rArray['time'] = date("H:i:s", $resultData['timestamp']);
					}
                        $recordArray[] = $rArray;
				 }
				 
				 $date = date("m/d/Y", $resultData['timestamp']);
				 $this->createRatesCSV($base, $date, $recordArray);
			}
		 }		 
		 
		 $time_end = microtime(true);
		 $execution_time = ($time_end - $time_start)/60;
         echo '<b>Total Execution Time:</b> '.$execution_time.' Mins';
		 die();
		 /*$allCurrencies = array_map(function($e) {return is_object($e) ? $e->code : $e['code'];}, $allCurrencies);
		 	
		 foreach($allCurrencies as $base)
		 {
			$currecyPairs = implode("=X,", preg_filter('/^/', $base, $allCurrencies)); 
			$resultData = $ratesmodel->getCurrentRates($currecyPairs);
			
			 foreach($resultData as $result)
			 {	
			    $currency = substr($result[0], 3, 3);
				
				if($base != $currency)
				{
					if(count($result) == 4 && $result[1] !='N/A')
					{
						$pair = $base.'/'.$currency;
						$ratesmodel->updateCurrentRates($pair,$result);
					}	
				}							 				 	 
			 }
		 }*/
				 
		 /*$params  = JComponentHelper::getParams('com_currencies');
		 $update_currencies = $params->get('update_currencies');
		 $updateList = explode(',', $update_currencies);
		 		 
		 foreach($updateList as $base)
		 {			
			 foreach($allCurrencies as $currency)
			 {
				 if($base != $currency)
				 {
					$result = $ratesmodel->getCurrent($base,$currency); 
					if(count($result) == 4 && $result[1] !='N/A')
					{
						$pair = $base.'/'.$currency;
						$ratesmodel->updateCurrentRates($pair,$result);
					}					
				 }				 	 
			 }
		 }		 
	}*/

	public function _updateRates($methodName, $resultData = null, $date = null)
	{
		$time_start = microtime(true);

		$model = JModelLegacy::getInstance('currency','CurrenciesModel');
		$ratesmodel = JModelLegacy::getInstance('rates','CurrenciesModel');
		$allCurrencies = $model->getCurrencies();
		$allCurrencies =  array_map(function($e) { return is_object($e) ? $e->code : $e['code'];}, $allCurrencies);
		$currencies = implode(",", $allCurrencies);

		$base = 'GBP';
		if (!$resultData) {
			$resultData = $ratesmodel->getCurrentRates($base, $currencies);
		}

		$recordArray = array();
		if(!empty($resultData['success']))
		{
			foreach($resultData['quotes'] as $key => $val)
			{
				$currency = substr($key, 3, 3);

				$resultArray[0] = $date ? strtotime($date . '23:59:59') : $resultData['timestamp'];
				$resultArray[1] = $val;
				$pair = $base.'/'.$currency;
				$ratesmodel->$methodName($pair,$resultArray);

				$rArray['pair'] = $base.$currency;
				$rArray['rate'] = $val;
				$rArray['date'] = date("m/d/Y", $resultData['timestamp']);
				$rArray['time'] = date("H:i:s", $resultData['timestamp']);
				$recordArray[] = $rArray;

			}

			$date = date("m/d/Y", $resultData['timestamp']);
			$this->createRatesCSV($base, $date, $recordArray);
		}
		$time_finish = microtime(true);
		echo 'Time of updating is ' . ($time_finish - $time_start) . 's';
	}

	public function updateRatesHistory($exchangeRates = null, $date = null)
	{
		$this->_updateRates('addRatesToHistory', $exchangeRates, $date);
	}
	
	public function updateAllRates(){
		$this->_updateRates('updateCurrentRates', null, null);


		 /*foreach($allCurrencies as $base)
		 {				   
				if($base == 'GBP')
				{
					continue;
				}
				$recordArray = array();
				
					foreach($allCurrencies as $data)
					 {				 
						$currency = $data;
						
						$relativeRate = $ratesmodel->getRelativeRate($base, $currency);
						
						if($base != $currency && !empty($relativeRate))
						{	
							$resultArray[0] = time();
							$resultArray[1] =  $relativeRate;
							$pair = $base.'/'.$currency;
							
							$ratesmodel->updateCurrentRates($pair,$resultArray);	
							
							$rArray['pair'] = $base.$currency;
							$rArray['rate'] = $relativeRate;
							$rArray['date'] = date("m/d/Y", time());
							$rArray['time'] = date("H:i:s", time());
							
							$recordArray[] = $rArray;
							
						}						
					 }
					 
					 $date = date("m/d/Y", time());
					 $this->createRatesCSV($base, $date, $recordArray);	
			
		 }*/	
		 
//		$curr = $ratesmodel->getCurrecnciesWithGBP();
//		//interconversion loop begins
//		if ( is_array( $curr ) && count( $curr ) > 0 )
//		{
//			foreach ( $curr as $arr )
//			 {
//				$recordArray = array();
//				foreach ( $curr as $arr1 )
//				{
//					if ( $arr1->currency2 == $arr->currency2 ) {
//					}
//					 else
//					{
//						$relativeRate = $arr1->rate / $arr->rate;
//						//echo "from ".$arr->currency2." to ".$arr1->currency2." rate ".$convert."<br/>";
//
//						$resultArray[0] = time();
//						$resultArray[1] =  $relativeRate;
//						$pair = $arr->currency2.'/'.$arr1->currency2;
//
////						$ratesmodel->updateCurrentRates($pair,$resultArray);
//
//						$rArray['pair'] = $arr->currency2.$arr1->currency2;
//						$rArray['rate'] = $relativeRate;
//						$rArray['date'] = date("m/d/Y", time());
//						$rArray['time'] = date("H:i:s", time());
//
//						$recordArray[] = $rArray;
//					}
//				}
//				 $date = date("m/d/Y", time());
//				 $this->createRatesCSV($base, $date, $recordArray);
//			}
//		}
//		die();
	}
	
	public function createRatesCSV($base, $date, $recordArray){
		 $destination = JPATH_SITE.'/rates/';
		 $year = date("Y", strtotime($date));
		 $month = date("F", strtotime($date));
		 $date = date("d", strtotime($date));
		 					
		 if(!is_dir($destination.$year))
		 {			
			 JFolder::create($destination.$year, 0755);			 
		 }
		 
		 if(!is_dir($destination.$year.'/'.$month))
		 {			
			 JFolder::create($destination.$year.'/'.$month, 0755);			 
		 }		 
		 
		 if(!is_dir($destination.$year.'/'.$month.'/'.$date))
		 {			
			 JFolder::create($destination.$year.'/'.$month.'/'.$date, 0755);			 
		 }

		 $fileName = $destination.$year.'/'.$month.'/'.$date.'/'.time().'_'.$base.'.csv';
		 $fp = fopen($fileName, 'w');

		 foreach ($recordArray as $fields) {
			fputcsv($fp, $fields);
		 }

		 fclose($fp);
	}

	public function updateURI()
	{
		$app = JFactory::getApplication();
		$output = 'index.php?option=com_currencies';
		$jinput = JFactory::getApplication()->input;
		$input = $jinput->getArray(array('view' => '', 'base' => '', 'value' => '', 'layout' => ''));
		foreach ($input as $key => $value)
		{
			$output .= '&' . $key . '=' . $value;
		}
		$url = JRoute::_($output);
		return $app->redirect($url);
	}

	public function updateLastYearRates()
	{
		$start = microtime(true);
		$ratesmodel = JModelLegacy::getInstance('rates','CurrenciesModel');
		$ratesmodel->deleteRatesData();
		foreach ($ratesmodel->getLastYearDates() as $day)
		{
			$ch = curl_init('https://apilayer.net/api/historical?access_key=1b557473e8cc9b2747c6037df7a993c8&currencies=&source=GBP&format=1&date='.$day);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$json = curl_exec($ch);
			curl_close($ch);

			$exchangeRates = json_decode($json, true);
			$this->updateRatesHistory($exchangeRates, $day);
		}
		$end = microtime(true);
		echo sprintf('The time of process is %s minutes', ($end - $start) / 60);
		return true;

	}
}
