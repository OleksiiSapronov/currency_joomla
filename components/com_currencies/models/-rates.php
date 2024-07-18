<?php
/*------------------------------------------------------------------------
# history.php - Currencies Component
# ------------------------------------------------------------------------
# author    Tom Mwenda, tommwenda@gmail.com
# copyright Copyright (C) 2014. All Rights Reserved
# license   GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
-------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
// import Joomla modelitem library
jimport('joomla.application.component.modelitem');

/**
 * Rates Model for Currencies Component
 */
class CurrenciesModelRates extends JModelItem
{

    public function getHistory($base, $currency2=null, $config=array(),$exchange_currency=null){
        
        $db = JFactory::getDbo();
		$groupBy =' date';
		if(isset($config['group_by'])){
			$groupBy = $config['group_by'];
		}
		/*$sql = 'select a.*,a.rate as Rate,a.date as Date,b.flag,b.name as country,c.name as currency_name ';
		$sql .=' from #__currencies_rates_current a left join #__countries b on a.currency2=b.currency_code ';
		$sql .=' left join #__currencies_all_currencies c on a.currency2=c.code ';*/
		
		$sql = 'select a.*,a.rate as Rate,a.date as Date,b.flag,b.name as country,c.name as currency_name, c.main, c.continent';
		$sql .=' from #__currencies_all_currencies c left join #__countries b on c.iso_alpha2=b.iso_alpha2 ';
		$sql .=' left join #__currencies_rates_current a on a.currency2=b.currency_code ';
//		$sql .='where base_currency=GBP';
		if($currency2)
		$sql .=' and currency2='.$db->quote($currency2);
//                if($exchange_currency)
               // $sql .=' and currency2 IN ('.$db->quote($exchange_currency).')';
		$sql.=' group by '.$groupBy;
	
		$sql .=' order by currency_name asc';
		if(isset($config['limit']))
		$sql .=' limit '.$config['limit'];
		$db->setQuery($sql);
		$history = $db->loadObjectList();
		return $history;
		
	}
    
    /*public function getCurrentBunch($base, $tos)
    {
      
    	$db = JFactory::getDbo();
    	$params  = JComponentHelper::getParams('com_currencies');
    	$queryArray = array();
    	foreach($tos as $c){
            
			$str = strtoupper($base.$c);
			$queryArray[] = '"'.$str.'"';
		}
		$queryString = implode(',', $queryArray);
		
    	$sql = "select * from #__currencies_all_currencies limit 5";
		$query ='http://query.yahooapis.com/v1/public/yql?q=select * from yahoo.finance.xchange where pair in ('.$queryString.')&env=store://datatables.org/alltableswithkeys';
		$xml = simplexml_load_file($query) or die("Exchange feed not loading!");
		$xml =json_decode(json_encode((array)$xml), TRUE);
		return $xml;
	}*/
	
	public function getCurrentRatesBunch($base, $tos)
    {
        $db = JFactory::getDbo();
		$sql = 'select * from #__currencies_rates_current where base_currency='.$db->quote($base).' and currency2 IN ("' . implode('", "', $tos) . '")';
        $db->setQuery($sql);
        $items = $db->loadObjectList();
    	
		return $items;
	}
	
   /* public function getCurrentBunch_reverse($base, $tos)
    {
        
    	$db = JFactory::getDbo();
    	$params  = JComponentHelper::getParams('com_currencies');
    	$queryArray = array();
    	foreach($tos as $c){
             $c;
			$str = strtoupper($c.$base);
			$queryArray[] = '"'.$str.'"';
		}
		$queryString = implode(',', $queryArray);
		
    	$sql = "select * from #__currencies_all_currencies limit 5";
		$query ='http://query.yahooapis.com/v1/public/yql?q=select * from yahoo.finance.xchange where pair in ('.$queryString.')&env=store://datatables.org/alltableswithkeys';
		$xml = simplexml_load_file($query) or die("Exchange feed not loading!");
		$xml =json_decode(json_encode((array)$xml), TRUE);
		return $xml;
	}*/

    public function getCurrent($base,$currency2)
    {
        /*$url ="http://finance.yahoo.com/d/quotes.csv?e=.csv&f=sl1d1t1&s=".$base.$currency2."=X";
      
        $data = file_get_contents($url);
        $data = explode(',',$data);*/
		$db = JFactory::getDbo();
        $sql = 'select * from #__currencies_rates_current where currency2='.$db->quote($base).' or currency2='.$db->quote($currency2);
        $db->setQuery($sql);
        $rate = $db->loadObjectList();
        $rate1 = $rate2 = 1;
        if (count($rate) == 2) {
	        $rate1 = $rate[0]->currency2 == $base ? $rate[0]->rate : $rate[1]->rate;
	        $rate2 = $rate[0]->currency2 == $currency2 ? $rate[0]->rate : $rate[1]->rate;
        } elseif ($base == 'GBP' && !($base == $currency2)) {
        	$rate2 = $rate[0]->rate;
        } elseif ($currency2 == 'GBP' && !($base == $currency2)) {
        	$rate1 = $rate[0]->rate;
        }
		$data = array();
		if(!empty($rate))
		{
			$data[0] = $base.$currency2;
			$data[1] = (float)$rate2 / (float)$rate1;
			$data[2] = date("m/d/Y",strtotime($rate[0]->date));
			$data[3] = date("h:i A",strtotime($rate[0]->date));
                }else{   
                        //if data not available with currency layer API , found with google API
                        if($base != $currency2){
                        $url ="https://www.google.com/finance/converter?a=1&from=".$base."&to=".$currency2;      
                        $res = file_get_contents($url);
                        preg_match("/<span class=bld>(.*)<\/span>/",$res, $converted);
                        if(count($converted)>0){
                        $converted = preg_replace("/[^0-9.]/", "", $converted[1]);
                        }
                        }else{
                            $converted = "1.00";
                        }
                        $data[0] = $base.$currency2;
			$data[1] = $converted;
			$data[2] = date("m/d/Y");
			$data[3] = date("h:i A");
                        
                }
        return $data;
    }
    
    public function checkIfHistoryExists($base,$currency2)
    {
        $db = JFactory::getDbo();
        $sql = 'select * from #__currencies_all_currencies where base_currency='.$db->quote($base).' and currency2='.$db->quote($currency2);
        $db->setQuery($sql);
        $items = $db->loadObjectList();
        return count($items);
           
    }
    
    public function updateRates($rates)
    {
        $db = JFactory::getDbo();
        $count = 0;
        $values = array();
        echo "<pre>";
		print_r($rates);
		exit;
        foreach($rates as $rate)
        {
                $baseCurrency = $db->quote($rate['ISO Code From']);
                $currency2 = $db->quote($rate['ISO Code To']);
                $value = $db->quote($rate['Rate']);
          
                $date = $db->quote(JFactory::getDate($rate['Date'])->toSql());
                $values[] =" ('',$baseCurrency,$currency2,$value,$date) ";
               
            
            $count++;
        }
        if(!empty($values)){
            $sql = 'insert into #__currencies_all_currencies values ';
            $sql.=implode(',', $values);
            $db->setQuery($sql);
            $db->execute();
       
        }
    }
    
    public function convert($base,$currency2){
       /* $db = JFactory::getDbo();
        $sql = 'select * from #__currencies_all_currencies values where base_currency='.$db->quote($base).' and currency2='.$db->quote($currency2).' order by ';
      */  
      $rate = $this->getCurrent($base, $currency2);
      return $rate;
    }
    
    public function getCurrencies($limit=0)
    {
        $db = JFactory::getDbo();
        $limit = $limit>0?"limit $limit ":"";
        $sql =' select a.*,(select flag from #__countries where currency_code=a.code limit 1) as flag from #__currencies_all_currencies a '.$limit;
        $db->setQuery($sql);
        return $db->loadObjectList();
    }
    
    public function getCurrency($code){
        $db = JFactory::getDbo();
        $sql =' select a.*,(select flag from #__countries where currency_code=a.code limit 1) as flag from #__currencies_all_currencies a where a.code='.$db->quote($code);
        $db->setQuery($sql);
        return $db->loadObject();
    }
    
    public function getChangeRate($current,$currency1, $currency2)
    {
    	$percent =0;
		$db = JFactory::getDbo();
		$sql = 'select * from #__rates_history where currency_from ='.$db->quote($currency1).' and currency_to='.$db->quote($currency2).' order by rid desc limit 1';
		$db->setQuery($sql);
		$history = $db->loadObject();
		
		if($history){
			$change = (float)$current-(float)$history->value;
			$percent = ($change/$history->value)*100;
		}
		return number_format($percent,2);
	}
    
    public function recordConversion($data){
        $db= JFactory::getDbo();
        $date = JFactory::getDate();
        
        $from = $db->quote($data['from']);
        $to = $db->quote($data['to']);
        $ip = $db->quote( $_SERVER['REMOTE_ADDR']);
        $date_created = $db->quote($date->toSql());
        
        $amount = $db->quote($data['amount']);
        $sql ='insert into #__currencies_lastconversion values ';
        $sql .="('',$from,$to,$amount,$ip,NOW(),1)";
        $db->setQuery($sql);
       
        $db->execute();
        return $db->getAffectedRows();
    }
	/**
     * @param $rates
     * @param int $days number of days to show on graph
     *  ['Day', 'Rates'],
        ['11mar',  1.3],
        ['12mar',  1.5],
        ['13mar',  1.4],
        ['14mar',  1.3]
     */
    public function getGraphData($rates, $days=5)
    {
        $return = new stdClass();
        $tmp = array();//will store rates here to help get minimum rate for this period
        $total = 0;
        $skip = 0;
        if($days>5){
            $skip = 2;
        }
        if($days>10 && $days<30){
            $skip = 5;
        }
        $interval = 8;
        $count =0;
        
        for($i=0; $i<$days; $i++)
        {
           if(!array_key_exists($i, $rates)){
             break;
           }
           // if($count==4 || $count==0){
            $rate=$rates[$i];
            $tmp[] = number_format($rate->rate,8, '.', '');
            $total = $total+$rate->rate;
            $date = date('Y-m-d', strtotime($rate->date));
            $data[]=" {x: '".$date."' , y: ". number_format($rate->rate,8, '.', '').'}';
            $goals[] = number_format($rate->rate,8, '.', '');
            $count=1;
           // }else{
               // $count++;
                //continue;
           // }
        }

//        $data = array_reverse($data);
//        array_unshift($data,"['Year', 'Rates']");
        $return->data = implode(',', $data);
        $return->goals = implode(',', $goals);
        $return->minValue = min($tmp);
        $return->maxValue = max($tmp);
        
        //get the dates of the min  values
        for($j=0; $j<$days; $j++)
        { 
            $rate=$rates[$j];
            if(number_format((float)str_replace(",","",$return->minValue),6, '.', '') == number_format((float)str_replace(",","",$rate->rate),6, '.', '')){
                $return->dateMin = date('M d', strtotime($rate->date));
                break;
            }
        }
//echo '-------------------------';
        //get the dates of the max  values
        for($k=0; $k<$days; $k++)
        {
            $rate=$rates[$k];
            if(number_format((float)str_replace(",","",$return->maxValue),6) == number_format((float)str_replace(",","",$rate->rate),6)){
                $return->dateMax = date('M d', strtotime($rate->date));
                break;
            }
        }
        $return->averange =  number_format($total / $days, 6);
        return $return;
    }
	
	/*public function updateCurrentRates($rates)
    {
        $db = JFactory::getDbo();
        
		foreach($rates as $rate)
		{
			$currency = explode('/', $rate['Name']);
			$date = date("Y-m-d H:i:s", strtotime($rate['Date']." ".$rate['Time']));
			
			if($currency[0] != $currency[1] && $rate['Name'] !='N/A')
			{
				$query = $db->getQuery(true);
				$query = "SELECT id FROM #__currencies_rates_current WHERE base_currency = '".$currency[0]."' and currency2 = '".$currency[1]."'";							
				$db->setQuery($query); 
				$row = $db->loadObject();
				
				if(!empty($row))
				{
					$query = $db->getQuery(true);
					$fields = array(
						$db->quoteName('rate') . ' = ' . $db->quote($rate['Rate']),
						$db->quoteName('date') . ' = ' . $db->quote($date)
					);

					$conditions = array(
						$db->quoteName('base_currency') . ' = ' . $db->quote($currency[0]), 
						$db->quoteName('currency2') . ' = ' . $db->quote($currency[1])
					);

					$query->update($db->quoteName('#__currencies_rates_current'))->set($fields)->where($conditions);	
					$msg = "Rate updated for: ".$rate['Name']."<br/>";
				}
				else
				{
					$query = $db->getQuery(true);
					$columns = array('base_currency', 'currency2', 'rate', 'date');
					$values = array($db->quote($currency[0]), $db->quote($currency[1]), $db->quote($rate['Rate']), $db->quote($date));
					$query
						->insert($db->quoteName('#__currencies_rates_current'))
						->columns($db->quoteName($columns))
						->values(implode(',', $values));	
					$msg = "Rate inserted for: ".$rate['Name']."<br/>";	
				}
				
				$db->setQuery($query);
				$result = $db->execute();
                echo $msg;				
			}			
		}
    }*/
	
	/* Method to get rates for all currecy pairs supplied to it */
	public function getCurrentRates($source, $currencies)
    {
        /*$url ="http://download.finance.yahoo.com/d/quotes.csv?e=.csv&f=sl1d1t1&s=".$currencies."=X";
				
		if (($handle = fopen($url, "r")) !== FALSE) 
		{
			while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				$spreadsheet_data[] = $data;
			}
			fclose($handle);
		}	
       return $spreadsheet_data;*/
	   
		$endpoint = 'live';
		$access_key = '1b557473e8cc9b2747c6037df7a993c8';

		$ch = curl_init('https://apilayer.net/api/'.$endpoint.'?access_key='.$access_key.'&source='.$source.'&currencies='.$currencies.'');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$json = curl_exec($ch);
		curl_close($ch);

		$exchangeRates = json_decode($json, true);
		return $exchangeRates;
	}

	public function deleteCurrentRates()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->delete($db->quoteName('#__currencies_rates_current'));

		$db->setQuery($query);

		if ($db->execute()) {
			return true;
		} else {
			return false;
		}
	}
	
	/* Method to update rates for all currecy pairs in database */
	public function updateCurrentRates($pair,$result)
    {
        $db = JFactory::getDbo();
        
		$currency = explode('/', $pair);
		//$dateStr =   str_replace('"', '', $result[2])." ".str_replace('"', '', $result[3]);			
		$date = date("Y-m-d H:i:s", $result[0]);
		
		$query = $db->getQuery(true);
		$query = "SELECT id FROM #__currencies_rates_current WHERE base_currency = '".$currency[0]."' and currency2 = '".$currency[1]."'";							
		$db->setQuery($query); 
		$row = $db->loadObject();
		
		if(!empty($row))
		{
			$query = $db->getQuery(true);
			$fields = array(
				$db->quoteName('rate') . ' = ' . $db->quote($result[1]),
				$db->quoteName('date') . ' = ' . $db->quote($date)
			);

			$conditions = array(
				$db->quoteName('base_currency') . ' = ' . $db->quote($currency[0]), 
				$db->quoteName('currency2') . ' = ' . $db->quote($currency[1])
			);

			$query->update($db->quoteName('#__currencies_rates_current'))->set($fields)->where($conditions);	
			$msg = "Rate updated for: ".$pair."<br/>";
		}
		else
		{
			$query = $db->getQuery(true);
			$columns = array('base_currency', 'currency2', 'rate', 'date');
			$values = array($db->quote($currency[0]), $db->quote($currency[1]), $db->quote($result[1]), $db->quote($date));
			$query
				->insert($db->quoteName('#__currencies_rates_current'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));	
			$msg = "Rate inserted for: ".$pair."<br/>";	
		}

		$db->setQuery($query);
		$queryresult = $db->execute();
		echo $msg;
    }

	/* Method to add rates to history */
    public function addRatesToHistory($pair, $result)
    {
	    $currency = explode('/', $pair);
	    $date = date("Y-m-d H:i:s", $result[0]);

	    $db = JFactory::getDbo();
	    $query = $db->getQuery(true);

	    $query
		    ->select('date')
		    ->from($db->quoteName('#__currencies_rates'))
		    ->where(array(
		    	$db->quoteName('date') . ' LIKE \'' . date("Y-m-d", $result[0]) . '%\'',
		    	$db->quoteName('currency2') . ' = ' . $db->quote($currency[1]),
			    ));

// Reset the query using our newly populated query object.
	    $db->setQuery($query);

// Load the results as a list of stdClass objects (see later for more options on retrieving data).
	    if (count($db->loadObjectList()) < 1) {
		    $query = $db->getQuery(true);
		    $columns = array('base_currency', 'currency2', 'rate', 'date');
		    $values = array($db->quote($currency[0]), $db->quote($currency[1]), $db->quote($result[1]), $db->quote($date));
		    $query
			    ->insert($db->quoteName('#__currencies_rates'))
			    ->columns($db->quoteName($columns))
			    ->values(implode(',', $values));
		    $db->setQuery($query);
		    return $db->execute();
	    }
    }
	
	/* Method to get rates relative to GBP */
	public function getRelativeRate($base, $currency)
    {
        $db = JFactory::getDbo();
		
		$query = $db->getQuery(true);
		$query = 'select * from #__currencies_rates_current where base_currency="GBP" and currency2='.$db->quote($base);
        $db->setQuery($query);
        $rate1 = $db->loadObject();
		
		$query = $db->getQuery(true);
		$query = 'select * from #__currencies_rates_current where base_currency="GBP" and currency2='.$db->quote($currency);
        $db->setQuery($query);
        $rate2 = $db->loadObject();
		
		$rate = '';		
		if($currency=='GBP')
		{
			$conversion = 1/$rate1->rate;
		    $rate = number_format((float)$conversion, 8, '.', '');
		}
		
		if(!empty($rate1) && !empty($rate2))
		{
			$conversion = $rate2->rate/$rate1->rate;
		    $rate = number_format((float)$conversion, 8, '.', '');
		}
    	
		return $rate;
	}
        
    public function getExchangeHistory($base, $currency2 = null, $config = array()) {
	    if ($base != $currency2) {
		    $db = JFactory::getDbo();
		    $limitStr = '';
		    $limit = $config['limit'];
		    if($limit>0)
			    $limitStr =' limit '.$limit;
		    $limit_date = date('Y-m-d', strtotime(date('Y-m-d') . " - " . $limit . " day"));
		    $sql = 'SELECT rate, currency2, date from #__currencies_rates where currency2 in (\'' . $base . '\', \'' . $currency2 . '\') and date > \'' . $limit_date . ' 00:00:00\' ORDER BY date ASC';
		    $db->setQuery($sql);
		    $history = $db->loadObjectList();
		    $result = [];

		    while (count($history) > 0) {
		    	if ($history[0]->currency2 == $base) {
				    $second_currency = array_shift($history);
				    $first_currency = array_shift($history);
			    } else {
				    $first_currency = array_shift($history);
				    $second_currency = array_shift($history);
			    }
			    $rate = new stdClass();
			    $rate->rate = (float)$first_currency->rate / (float)$second_currency->rate;
		    	$rate->date = $first_currency->date;
			    array_push($result, $rate);
		    }
		    return $result;
	    } else {
	    	return false;
	    }
    }
	
	/* Method to get all currencies with GBP as base*/
	 public function getCurrecnciesWithGBP() {
        $db = JFactory::getDbo();		
		$query = $db->getQuery(true);
		$query = 'select * from #__currencies_rates_current where base_currency="GBP"';
        $db->setQuery($query);
        $result = $db->loadObjectList();
        return $result;
    }
    
	/* Method to get other currencies */
	 public function getOtherCurrencies() {
                $db = JFactory::getDbo();		
		$query = $db->getQuery(true);
		$query = 'SELECT * FROM `#__currencies_all_currencies` WHERE main=1 order by name limit 15';
                $db->setQuery($query);
                $result1 = $db->loadObjectList();
        
		$query1 = $db->getQuery(true);
		$query1 = 'SELECT * FROM `#__currencies_all_currencies` WHERE main<>1 order by rand() limit 5';
                $db->setQuery($query1);
                $result2 = $db->loadObjectList();
                
                $result = array_merge($result1, $result2);
        return $result;
    }

	/*Method to get last year days */
	public function getLastYearDates()
	{
		$now = date('Y-m-d');
		$yearAgo = date('Y-m-d', strtotime($now . " - 30 day"));

		$dayPlusOne = $yearAgo;
		$daysOfLastYear = [];
		for ($i = 0; $i < 30; $i++)
		{
			$dayPlusOne = date('Y-m-d', strtotime($dayPlusOne . " + 1 day"));
			array_push($daysOfLastYear, $dayPlusOne);
		}

		return $daysOfLastYear;
	}

	public function deleteRatesData()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__currencies_rates'));
		$db->setQuery($query);
		return $db->execute();
	}
}