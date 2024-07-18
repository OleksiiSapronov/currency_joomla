<?php 

class CurrencypairsHelper{
    static function getCurrencies($params)
    {   
        $currencies = array();
        $limitNum = $params->get('limit');
        $fetchSpecific = $params->get('specific');
        
        if($limitNum>0){
            $limit ='limit '.$limitNum;
        }else{
            $limit ='';
        }
        if($fetchSpecific){
            $db = JFactory::getDbo();
            $currencies = $params->get('specific_currencies');
            $currencies = explode(',', $currencies);
            $currencies = array_map('trim', $currencies);
            $currenciesPopular = implode('", "', $currencies);
            $sql ='select currency_code,flag,b.name,currency_name from #__countries as a INNER JOIN #__currencies_all_currencies as b ON a.iso_alpha2=b.iso_alpha2 WHERE `code` IN ("' . $currenciesPopular . '") ORDER BY FIELD(`code`, "'. $currenciesPopular .'") '.$limit;
            $db->setQuery($sql);
            $currencies = $db->loadObjectList();
            
            $db_main = JFactory::getDbo();
            $sql_main ='select a.currency_code,a.flag,b.name,a.currency_name from #__countries as a INNER JOIN #__currencies_all_currencies as b ON a.iso_alpha2=b.iso_alpha2 WHERE b.main=1 ORDER BY a.name ASC '.$limit;
            $db_main->setQuery($sql_main);
            $currencies_main = $db_main->loadObjectList();
            
            $db_random = JFactory::getDbo();
            $sql_random ='select a.currency_code,a.flag,b.name,a.currency_name from #__countries as a INNER JOIN #__currencies_all_currencies as b ON a.iso_alpha2=b.iso_alpha2 WHERE `code` NOT IN ("' . $currenciesPopular . '") AND b.main<>1 ORDER BY rand() ASC LIMIT 2';
            $db_random->setQuery($sql_random);
            $currencies_random = $db_random->loadObjectList();
            
            $currencies = array_merge( $currencies, $currencies_main );
            $currencies = array_map("unserialize", array_unique(array_map("serialize", $currencies)));
            array_splice($currencies,$limitNum);
            $currencies = array_merge( $currencies, $currencies_random );
        }else{
            $db = JFactory::getDbo();
           // $sql ='select a.*,b.flag from #__currencies_all_currencies a left join #__countries b on a.code =b.currency_code '.$limit;
            //$sql ='select a.*,(select flag from #__countries where currency_code=a.code limit 1) as flag from #__currencies_all_currencies a '.$limit;
//            $sql ='select currency_code,flag,name,currency_name from #__countries ORDER BY id_countries DESC '.$limit;
            $sql ='select a.currency_code,a.flag,b.name,a.currency_name from #__countries as a INNER JOIN #__currencies_all_currencies as b ON a.iso_alpha2=b.iso_alpha2 WHERE b.main=1 ORDER BY a.name ASC '.$limit;
            $db->setQuery($sql);
            $currencies = $db->loadObjectList();
           
        }
       
        return $currencies;
    }
}