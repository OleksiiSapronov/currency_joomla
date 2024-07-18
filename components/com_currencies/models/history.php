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
 * History Model for Currencies Component
 */
class CurrenciesModelHistory extends JModelItem
{

    public function getRates($currency1, $currency2,$limit=0 ){
        $db = JFactory::getDbo();
        $limitStr = '';
        if($limit>0)
            $limitStr =' limit '.$limit;
        $sql ='select DISTINCT CAST(a.date AS DATE) AS dateonly,a.id,a.base_currency,a.currency2, a.rate as Rate,a.date as Date  from #__currencies_rates a where a.base_currency='.$db->quote($currency1).' and a.currency2='.$db->quote($currency2).' group by dateonly '.$limitStr;
        $db->setQuery($sql);
        $items = $db->loadAssocList();
        if(empty($items)){
            //echo "fetching from online...";
        $ratesModel = JModelLegacy::getInstance('rates','CurrenciesModel');
        $items= $ratesModel->getHistory($currency1,$currency2);
        }else{
            //echo "fetching from local";
        }
        return $items;
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
		/*$newrates = (array)$rates[0];
		//foreach ( $newrates as $v )
		//{			
		  $newrates['Date'] = $newrates['date'];
		  unset($newrates['date']);
		  $rates[0] = $newrates;
		  $newrates['Rate'] = $newrates['rate'];
		  unset($newrates['rate']);
		  $rates[0] = $newrates;
		//}*/
		$rates = json_decode(json_encode($rates),TRUE);
		
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
            $tmp[] = $rate['Rate'];
            $total = $total+$rate['Rate'];
            $date = date('M d', strtotime($rate['Date']));
            $data[]=" ['".$date."', ". $rate['Rate'].']';
            $count=1;
           // }else{
               // $count++;
                //continue;
           // }
        }
        $data = array_reverse($data);
        array_unshift($data,"['Year', 'Rates']");
        $return->data = implode(',', $data);
        $return->minValue = min($tmp);
        $return->maxValue = max($tmp);
        
        //get the dates of the min  values
        for($j=0; $j<$days; $j++)
        {
            $rate=$rates[$j];
            if($return->minValue==$rate['Rate']){
                $return->dateMin = date('M d', strtotime($rate['Date']));
                break;
            }
        }

        //get the dates of the max  values
        for($k=0; $k<$days; $k++)
        {
            $rate=$rates[$k];
            if($return->maxValue==$rate['Rate']){
                $return->dateMax = date('M d', strtotime($rate['Date']));
                break;
            }
        }
        $return->averange =  $total / $days;
        return $return;
    }
    
   
}