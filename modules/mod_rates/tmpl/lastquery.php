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
$itemId = 158;//todo --> put this on config

require_once('helper.php');
require_once(JPATH_ROOT.'/components/com_currencies/helpers/currencies.php');
JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_currencies/models');
$historyModel = JModelLegacy::getInstance('history','CurrenciesModel');
$items = ModRatesHelper::getList();
$numRows = $params->get('max_last_query_count');
?>
<div class="row-fluid">
    <?php

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
        $url = JRoute::_('index.php?option=com_currencies&view=pairs&base='.$from->code.'&to='.$to->code.'&Itemid='.$itemId.'&value='.$value);
        ?>
        <div class="span12"><a href="<?php echo $url ?>"><?php echo $value ?> <?php echo $from->code.' '.$to->code ?> <?php echo $minutes ?> minutes ago</a></div>
        <?php
        ?>
    <?php } ?>
</div>



