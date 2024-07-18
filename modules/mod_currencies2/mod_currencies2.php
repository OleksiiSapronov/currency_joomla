<?php
defined('_JEXEC') or die('Restricted access');


$doc = JFactory::getDocument();
$doc->addStyleSheet('components/com_currencies/tmpl/default.css');

$appWeb      = new JApplicationWeb;



$lang = JFactory::getLanguage();
$lang->load('com_currencies');


$maincurrency = $params->get('maincurrency');

$currencies = array();
for($i = 1; $i<=10; $i++)
{
    if($appWeb->client->mobile && $i>4)
        break;
   $currency = $params->get('currency' . $i);
   if(!empty($currency))
   {
      $link = $params->get('currency' . $i . 'link');
      if(!empty($link))
      {
         if( strpos('http://', $link) === false)
         {
            $link = 'http://' . $link;
         }
      }
      $currencies[] = array('currency'=>$currency, 'link'=>$link, 'kurs'=>0, 'kurs_odwrotny'=>0);
   }
}


require_once 'components/com_currencies/controller.php';

if(!empty($maincurrency) && !empty($currencies))
{
   CurrenciesController::getdata_currencies2($maincurrency, $currencies);
   require(JModuleHelper::getLayoutPath('mod_currencies2'));
}

