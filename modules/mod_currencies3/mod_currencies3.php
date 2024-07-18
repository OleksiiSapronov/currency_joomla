<?php

defined('_JEXEC') or die('Restricted access');

$doc =JFactory::getDocument();

$doc->addStyleSheet('components/com_currencies/tmpl/default.css');
$doc->addStyleSheet(JUri::base().'components/com_currencies/assets/style.css');
$doc->addScript('https://code.jquery.com/ui/1.11.3/jquery-ui.min.js');


require_once 'components/com_currencies/controller.php';



$lang = JFactory::getLanguage();

$lang->load('com_currencies');







$cols = array();

for($i = 1; $i<=15; $i++)

{

   $currency = $params->get('currencycol' . $i);

   if(!empty($currency))

   {      

      $cols[] = $currency;

   }

}







$currencies = array();

for($i = 1; $i<=40; $i++)

{

   $currency = $params->get('currencyrow' . $i);

   if(!empty($currency))

   {

      $link = $params->get('currencyrow' . $i . 'link');

      if(!empty($link))

      {

         if( strpos('http://', $link) === false)

         {

            $link = 'http://' . $link;

         }

      }

            

      $currencies[$currency] = array('currency'=>$currency, 'link'=>$link);

   }

}









require(JModuleHelper::getLayoutPath('mod_currencies3'));