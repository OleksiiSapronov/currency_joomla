<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_custom
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$paramcurrencies = $params->get('specific_currencies');
$paramcurrencies = explode(',', $paramcurrencies);
$numSpecificCurrencies = count($paramcurrencies);
$i=0;
echo '<h4>'. JText::_('MAIN_CURRENCIES'). '</h4> ';
?>

{module Reklama_LINKI}

<?php


$currencies = array_unique($currencies,SORT_REGULAR);
foreach($currencies as $currency){
   if($i==$numSpecificCurrencies) echo '<br><h4>'. JText::_('OTHER_CURRENCIES'). '</h4>';
    $db = JFactory::getDBO();
 $sql = "SELECT name FROM #__currencies_all_currencies where code = '$currency->currency_code'";
$db->setQuery($sql);  
$options = $db->loadObjectList();


// $sql = "SELECT name FROM #__currencies where code = '$currency->currency_code'";
//$db->setQuery($sql);  
//$options = $db->loadObjectList();

//
//  if(!empty($options))
//  {
?>
<div class="row-fluid">

<ul style="list-style: none;">
    <li class="currency-flag">
        <a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base='.$currency->currency_code.'&layout=landing&Itemid='.$params->get('itemid')); ?>">


   <img src="components/com_currencies/assets/images/blank.gif" class="flag flag-<?php echo strtolower(substr($currency->flag, 0, 2)); ?>" alt="<?php echo $currency->name?>" /> 	


   
			
			
                <?php 
                //echo $currency->name.' '.$currency->currency_name;
                echo JText::_('MAIN_'.strtoupper($currency->name))." (".$currency->currency_code.")";
//                echo $options[0]->name  ;
                ?></a>
    </li>
	</ul>

	
	
	</div>
  <?php

//  } 
  $i++;
  }


  
  ?>
{module Reklama_GOOGLE300x600}


