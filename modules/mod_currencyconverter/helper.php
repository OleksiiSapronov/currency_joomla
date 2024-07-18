<?php 

class CurrencyConverterHelper{
    static function getList()
    {
        $db = JFactory::getDbo();
        $sql ='select * from #__currencies_all_currencies';
        $db->setQuery($sql);
        $currencies = $db->loadObjectList();
        return $currencies;
    }
    
    public static function getConversions($params){
        $limit = $params->get('max_count',10);
        $db_rand = JFactory::getDbo();
        $sql_rand = 'select * from #__currencies_lastconversion WHERE `is_real`=0 order by date_created desc limit 5';
        $db_rand->setQuery($sql_rand);
        $db_rand->execute();
        $items_rand = $db_rand->loadObjectList();
        
        $db = JFactory::getDbo();
        $sql = 'select * from #__currencies_lastconversion WHERE `is_real`=1 order by date_created desc limit '.$limit;
        $db->setQuery($sql);
        $db->execute();
        $items = $db->loadObjectList();
        
        $items = array_merge(  $items, $items_rand );
        $items = array_map("unserialize", array_unique(array_map("serialize", $items)));
        return $items;
    }
    
    public static function getStats($currencyCode)
    {
		JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_currencies/models');
		$currencyModel = JModelLegacy::getInstance('currency','CurrenciesModel');
		 $ratesmodel = JModelLegacy::getInstance('rates','CurrenciesModel');
		 $params  = JComponentHelper::getParams('com_currencies');
		 
		 $featured = $params->get('featured_currencies');
		 $featured = explode(',', $featured);
		 if(in_array($currencyCode, $featured)){
		 	foreach($featured as $key=> $f){
		 		if($f==$currencyCode){
					unset($featured[$key]);
					break;
				}
				
			}
		 	$db = JFactory::getDbo();
		 	$sql='select code from #__currencies_all_currencies where code<>'.$db->quote($currencyCode).' order by rand() limit 1';
		 	$db->setQuery($sql);
		 	$result = $db->loadObject();
		 	$featured[] = $result->code;
		 }
		$currency = $currencyModel->getByCode($currencyCode);
		$result = $ratesmodel->getCurrentRatesBunch($currencyCode, $featured);
		//$result = $result['results']['rate'];
		
		$stats = array();
		$stats['title'] = $currency->code;
		$stats['stats'] = $result;
		return $stats;
	}
	
	public static function setSeoData(){
		$db = JFactory::getDbo();
        JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_currencies/models');
		$currencyModel = JModelLegacy::getInstance('currency','CurrenciesModel');
	   
	    $currencies = $currencyModel->getCurrencies();
		$allCurrencies =  array_map(function($e) { return is_object($e) ? $e->code : $e['code'];}, $currencies);
		
		$random_keys=array_rand($allCurrencies,3);
		$date = date("Y-m-d H:i:s");
		$ip = $_SERVER['REMOTE_ADDR'];
		foreach($random_keys as $key)
		{
			$amount = mt_rand(1,100000);
			
			$to = $key+10;
			if (!array_key_exists($to,$allCurrencies))
			{
				$to = $key-10;
			}
			
			$query = $db->getQuery(true);
			$columns = array('from', 'to', 'amount', 'ip', 'date_created','is_real');
			$values = array($db->quote($allCurrencies[$key]), $db->quote($allCurrencies[$to]), $db->quote($amount), $db->quote($ip), $db->quote($date),0);
			$query
				->insert($db->quoteName('#__currencies_lastconversion'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));	
			$db->setQuery($query);
		    $db->execute();	
		}        
    }
    
    public function getChangeRate($current,$currency1, $currency2)
    {
                $percent =0;
                $change = new stdClass();
		$db = JFactory::getDbo();
		$sql = 'select * from #__currencies_rates where base_currency ='.$db->quote($currency1).' and currency2='.$db->quote($currency2).' and date < CURDATE() order by id desc limit 1';
		
		$db->setQuery($sql);
		$history = $db->loadObject();
                
		if($history){
			$diff = (float)$current-(float)$history->rate;
			$percent = ($diff/$history->rate)*100;
		}
                $change->percent = number_format($percent,2);
                $change->yesterday = $history->rate;
		return $change;
    }
    
    
}