<?php

class ModRatesHelper{
    static function getList()
    {
        $db = JFactory::getDbo();
        $sql ='select * from #__all_currencies';
        $db->setQuery($sql);
        $currencies = $db->loadObjectList();
        return $currencies;
    }

    public function getRandoItem(){

    }


}