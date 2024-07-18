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
class CurrenciesModelCurrency extends JModelItem
{
   function getById($id){
   	$db = JFactory::getDbo();
   	$sql ='select * from #__currencies_all_currencies where id = '.$db->quote($id);
   	$db->setQuery($sql);
   	return $db->loadObject();
   }
   
    public function getByCode($code){
        $db = JFactory::getDbo();
        //$extra = ' (select flag from #__countries where currency_code=a.code limit 1) as flag, ';
		$extra = ' (select flag from #__countries where iso_alpha2=a.iso_alpha2 limit 1) as flag, ';
        $extra .= ' (select currency_name from #__countries where currency_code=a.code limit 1) as currency_name ';
        //$extra .= ' (select currrency_symbol from #__countries where currency_code=a.code limit 1) as symbol ';
        $sql =' select a.*,'.$extra.' from #__currencies_all_currencies a where a.code='.$db->quote($code);
        $db->setQuery($sql);
        return $db->loadObject();
    }
    
    public function getCurrencies($limit=0)
    {
        $db = JFactory::getDbo();
        $limit = $limit>0?"limit $limit ":"";
        //$sql =' select a.*,(select flag from #__countries where currency_code=a.code limit 1) as flag from #__currencies_all_currencies a '.$limit;
        $sql =' select a.*,(select flag from #__countries where iso_alpha2=a.iso_alpha2 limit 1) as flag from #__currencies_all_currencies a order by a.code'.$limit;
 	    $db->setQuery($sql);
        return $db->loadObjectList();
    }
	
    public function getCurrenciesByCodes($codes)
    {
        $db = JFactory::getDbo();
        $codes_str = implode("','",$codes);
        //$sql =' select a.*,(select flag from #__countries where currency_code=a.code limit 1) as flag from #__currencies_all_currencies a ';
		$sql =' select a.*,(select flag from #__countries where iso_alpha2=a.iso_alpha2 limit 1) as flag from #__currencies_all_currencies a ';
		$sql .= " where a.code in('".$codes_str."') ORDER BY FIELD(`code`, '".$codes_str."')";
        $db->setQuery($sql);		
        return $db->loadObjectList();
    }	 
    
    public function getLastUpdateDate($base)
    {
        $db = JFactory::getDbo();
	$query = $db->getQuery(true);
        $query = 'select date from #__currencies_rates_current where base_currency="'.$base.'" limit 1';
        $db->setQuery($query);
        $lastUpdateDate = $db->loadObject();
        return $lastUpdateDate->date;
    }
    
    public function getContinents()
    {
        $db = JFactory::getDbo();
	$query = $db->getQuery(true);
        $query = 'SELECT DISTINCT continent from #__currencies_all_currencies';
        $db->setQuery($query);
        $continents = $db->loadAssocList();
        $continents = array_column($continents, 'continent');
        return $continents;
    }	 
    public function getCountriesByCurrencyCode($code)
    {
        $db = JFactory::getDbo();
	$query = $db->getQuery(true);
        $query = 'SELECT DISTINCT name from #__countries where currency_code= "'.$code.'"';
        $db->setQuery($query);
        $countries = $db->loadAssocList(); 
          $countries = array_column($countries, 'name');
        $countries = implode (", ", $countries);
        return $countries;
    }	 
}