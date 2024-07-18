<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_custom
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$date = JFactory::getDate();
$app = JFactory::getApplication();
$doc = JFactory::getDocument();
$doc->addScript(JUri::root().'components/com_currencies/assets/js/timeago.js');
$doc->addScriptDeclaration('
jQuery(document).ready(function() {
  jQuery("abbr.timeago").timeago();
  
});
');

{$all=new \NumberFormatter("en-EN", \NumberFormatter::DECIMAL);}

$itemId = 158;//todo --> put this on config
require_once(JPATH_ROOT.'/components/com_currencies/helpers/currencies.php');

// Setting random entries in database for seo purpose
CurrencyConverterHelper::setSeoData();

JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_currencies/models');
$historyModel = JModelLegacy::getInstance('history','CurrenciesModel');
$conversions =CurrencyConverterHelper::getConversions($params);
?>
<div class="row-fluid" style="padding:5px">

    <?php
    if(!empty($conversions)){
        foreach($conversions as $conv){
             $url = JRoute::_('index.php?option=com_currencies&view=pairs&base='.$conv->from.'&to='.$conv->to.'&value='.ceil($conv->amount).'&Itemid='.$itemId);
       
        ?>
        <div class="span12" style="border-bottom:1px solid #ccc;padding:4px" >
            <a href="<?php echo $url ?>"><?php echo number_format((float)$conv->amount,countDecimals($conv->amount)).' '.$conv->from.'-'.$conv->to ?> </a><br><abbr class="timeago localtime small muted" title="<?php echo gmdate("Y-m-d\TH:i:s\Z", strtotime($conv->date_created)); ?>"><?php echo $conv->date_created ?>
        </div>
    <?php
        }
    }
  

  
/*
    for($i=0; $i<$numRows; $i++){
        $fromIndex = array_rand($items,1);
        $toIndex = array_rand($items,1);
        $from = $items[$fromIndex];
        $to = $items[$toIndex];
        if($i==0){
            $max = 1;
        }elseif($i>1 && $i<=4){
            $max = 4;
        }else{
            $max = $i+1;
        }
        $minutes = rand($i, $max);
        do{
            $toIndex = array_rand($items,1);
            $to = $items[$toIndex];
        }while($from->code==$to->code);

        $to = $items[$toIndex];
        $itemCurrencies = $historyModel->getRates($from->code,$to->code);
        if(empty($itemCurrencies))
            continue;
        $value = rand(1,rand(2,20000));
        $roundOf = rand(1,0);
        $value = $roundOf?ceil($value / 10) * 10:$value;
        $url = JRoute::_('index.php?option=com_currencies&&base='.$from->code.'&to='.$to->code.'&Itemid='.$itemId.'&value='.$value);
        */?>
        <!--<div class="span12"><a href="<?php echo $url ?>"><?php echo $value ?> <?php echo $from->code.' '.$to->code ?> <?php echo $minutes ?> minutes ago</a></div>
        -->
    <?php //} ?>
</div>



