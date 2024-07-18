<?php
defined('_JEXEC') or die;

class CurrenciesModelAbout extends JModelLegacy{
	
	function getCurrencyArticle($code)
	{
		$db = JFactory::getDbo();
		$sql = 'select * from #__content where id in(select article_id from #__currencies_all_currencies where code='.$db->quote($code).')';
		$db->setQuery($sql);		
		$data = $db->loadObject();		
		return $data;
	}
}