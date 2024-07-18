<?php 
$doc = JFactory::getDocument();
$doc->addScript(JUri::root().'components/com_currencies/assets/js/holder.min.js');
$date = JFactory::getDate();
$today = $date->toISO8601();
$today = date('M d, Y', strtotime($today));
$decimal=$this->params->get('decimal_places');
$lang1="pl";
$params  = JComponentHelper::getParams('com_currencies');
$dropdown_currencies = $params->get('dropdown_currencies');
$dropdown_currencies = explode(',', $dropdown_currencies);
$currenciesModel =  JModelLegacy::getInstance('currency','CurrenciesModel');
$top_currencies = $currenciesModel->getCurrenciesByCodes($dropdown_currencies);
$lastUpdateDate = $currenciesModel->getLastUpdateDate($this->baseCurrency->code);
if ($lang1=="pl") {$com1=","; $com2=" ";} 
if ($lang1=="en") {$com1="."; $com2=",";} 

?>
    <div class="currenciesc container-fluid">
<div class="row top-section">    
        <div class="col-sm-12 welcome">
           <h1>    <?php vprintf(JText::_('COM_CURRENCIES_HISTORY_PAGE_TITLE'),array($this->baseCurrency->name ,$this->currency2->name)); ?>  </h1>

            </div>
        <div class="col-sm-6 ad">
           <?php 
			$position = 'advert1';
			$modules =& JModuleHelper::getModules($position); 
			foreach ($modules as $module) { 
			  echo JModuleHelper::renderModule($module); 
			} 3
			?>
        </div>
        <div  class="col-sm-6 rate well"> 
          
             <table width="100%" cellspacing="5" cellpadding="5" align="center" ">
			<tbody><tr style="color:#F96010;">
				<td style="text-align: right;"><div class="currencytitle">
				<h2 style="color:#F96010;" > <?php echo $this->value ?>	<?php echo $this->baseCurrency->code ?></div></h2></td>
				<td style="text-align: center;"><div class="currencytitle"><h2>=</h2></div></h2></td>
								<td style="text-align: left;"><div class="currencytitle">
			<h2 style="color:#F96010;" >	<?php echo number_format(($this->current[1]*$this->value),$decimal,$com1,$com2).' '.$this->currency2->code?></div>
				 				</h2> </td>
			</tr>
			<tr>
				<td class="currencyname" style="text-align: right;">
					<a href="<?php echo $this->baseCurrency->page ?>">  <?php echo $this->baseCurrency->name ?> </a>
	
				</td>
				<td></td>
				<td class="currencyname" style="text-align: left">
					<a href="<?php echo $this->currency2->page ?>"> <?php echo $this->currency2->name ?> </a>
				</td>
			</tr>
			<tr>
				<td style="text-align: center;" colspan="3">
				<div class="currencyone"> 1 <?php echo $this->baseCurrency->name ?> (<?php echo $this->baseCurrency->symbol ?>) = <?php echo $this->current[1] ?> <?php echo $this->currency2->currency_name ?> (<?php echo $this->currency2->symbol ?>)</div></td>
			</tr>
			<tr>
				<td style="text-align: center;" colspan="3">
				<div class="currencyone">
				 1 <?php echo $this->currency2->name ?>  ( <?php echo $this->currency2->symbol ?> ) = <?php  echo $this->inverse[1]?>
				 <?php echo $this->baseCurrency->name ?>  ( <?php echo $this->baseCurrency->symbol ?> )</div>
				</td>
			</tr>
			<!--<tr>
				<td class="currencyname" style="text-align: right;">
					<a href="<?php echo $this->baseCurrency->article ?>"> <?php vprintf(JText::_('COM_CURRENCIES_MORE_ABOUT_CURRENCY'),array($this->currency2->name))?></a></td>
				<td></td>
				<td class="currencyname" style="text-align: left">
				<a href="<?php echo $this->currency2->article ?>"><?php vprintf(JText::_('COM_CURRENCIES_MORE_ABOUT_CURRENCY'),array($this->currency2->name)) ?></a></td>
			</tr>-->
                        <tr>
                            <td colspan="3"><?php echo JText::_('COM_CURRENCIES_OUR_MONEY_CONVERTER') ?><?php echo JText::_('COM_CURRENCIES_LAST_UPDATE'). date('M d, Y', strtotime($lastUpdateDate));?></td>
                        </tr>
		</tbody></table>
        </div>
    </div>
<!-- SEARCH FORM PART START-->
    <div class="row">
        <div class="col-sm-12">

            <form class="form-inline form-validate" action="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&task=rates.convert&Itemid='.$this->params->get('pairs_itemid')) ?>" method="post">
                <div class="form-group">

                    <label class="sr-only" for="inputamount">Amount</label>

                    <input type="number" step="0.01" name="value" value="<?php if (JRequest::getVar('value')){ echo JRequest::getVar('value'); }else{ echo 1; } ?>" class="form-control required" id="inputamount" placeholder="Amount" lang="en-150">

                </div>
                <div class="form-group">
                    <label class="sr-only" for="currencyselect_fromd">From</label>
                    <select class="required input-large" name="base" id="currencyselect_fromd">
                                     <optgroup label="Recent">
                                     <?php foreach($top_currencies as $top_currency): ?>
                                     <option <?php echo $top_currency->code==$this->baseCurrency->code?'selected="selected"':'' ?> value="<?php echo $top_currency->code ?>" data-image="<?php echo Juri::root().'images/flags/'.$top_currency->flag ?>">
                                    <?php echo $top_currency->code ?> - <?php echo $top_currency->name ?>
                                    </option>
                                     <?php endforeach; ?>
                                     </optgroup>

                                     <optgroup label="All Currencies">				 
                                     <?php foreach($this->allCurrencies as $cfrom){ ?>
                                     <option <?php echo $cfrom->code==$this->baseCurrency->code?'selected="selected"':'' ?> value="<?php echo $cfrom->code ?>" data-image="<?php echo Juri::root().'images/flags/'.$cfrom->flag ?>">
                                     <?php echo $cfrom->code ?> - <?php echo $cfrom->name ?>
                                     </option>
                                     <?php } ?>
                                     </optgroup>
                   </select>
                </div>

                <div class="form-group">
                    <label class="sr-only" for="convertto">To</label>
                    <select name="to" class="required input-large" id="currencyselect_tod">
                                     <optgroup label="Recent">
                                     <?php foreach($top_currencies as $top_currency): ?>
                                     <option <?php echo $top_currency->code==$this->currency2->code?'selected="selected"':'' ?> value="<?php echo $top_currency->code ?>" data-image="<?php echo Juri::root().'images/flags/'.$top_currency->flag ?>">
                                     <?php echo $top_currency->code ?> - <?php echo $top_currency->name ?>
                                     </option>
                                     <?php endforeach; ?>
                                     </optgroup>

                                     <optgroup label="All Currencies">
                                     <?php foreach($this->allCurrencies as $cto){ ?>
                                     <option  <?php echo $cto->code==$this->currency2->code?'selected="selected"':'' ?>  value="<?php echo $cto->code ?>" data-imagesrc="<?php echo Juri::root().'images/flags/'.$cto->flag ?>"
                     data-description="<?php echo $cto->name ?>"><?php echo $cto->code ?> - <?php echo $cto->name ?></option>
                                     <?php } ?>
                                     </optgroup>
                            </select>
                </div>
                <button type="submit" class="btn btn-primary validate">Convert</button>
                <input type="hidden" value="com_currencies" name="option">
                <input type="hidden" value="pairs" name="view">
                <input type="hidden" value="rates.convert" name="task">
            </form>
        </div>
    </div>
<!-- SEARCH FORM PART END-->

<div class="row">
    <div class="col-sm-12" style="margin-top:20px">
       
            <h3 class="center"> <?php vprintf(JText::_("COM_CURRENCIES_EXCHANGE_GRAPH_HISTORY_TITLE"),array($this->historyDays)) ?> </h3>

           <div id="chart_div"> </div>
        
        <div style="margin: 5px" class="center">
<?php 
if ($this->graph->maxValue<1) { $decimal=3; } 
if ($this->graph->maxValue<0.01) { $decimal=4; } 
if ($this->graph->maxValue<0.001) { $decimal=5; } 
if ($this->graph->maxValue<0.0001) { $decimal=6; } 
?>		
        <strong><?php echo JText::_("COM_CURRENCIES_MIN")?></strong>=<?php if($this->graph->minValue > 1){echo number_format($this->graph->minValue,3,$com1,$com2); } else { echo number_format($this->graph->minValue,6,$com1,$com2);} ?> [<?php echo $this->graph->dateMin ?>] <strong><?php // echo JText::_("COM_CURRENCIES_AVG") ?>
        </strong><?php //echo number_format($this->graph->averange,$decimal,$com1,$com2) ?> <strong><?php echo JText::_("COM_CURRENCIES_MAX") ?></strong>=<?php if($this->graph->maxValue > 1){echo number_format($this->graph->maxValue,3,$com1,$com2); } else { echo number_format($this->graph->maxValue,6,$com1,$com2);} ?> [<?php echo $this->graph->dateMax ?>]</div>
    </div>
</div>
<div class="row">
<div class="col-sm-12">
    <h3 class="center"><?php echo JText::_('COM_CURRENCIES_HISTORICAL_RATES') ?></h3>
<table class="table table-striped table-bordered">
    <th><?php echo JText::_('COM_CURRENCIES_DATE') ?></th>
    <th> <?php echo $this->baseCurrency->code ?></th>
    <th> <?php echo $this->currency2->code ?></th>
    <?php $count =0; ?>
    <?php foreach($this->rates as $rate){
		$rate = (array)$rate;
	
	        $value = (float)($rate['Rate'].' '.$this->currency2->code);


			$rowClass = ($count%2==0)?"even":"odd"
        ?>
        <tr class="<?php echo $rowClass?>">
            <td>
                <?php echo date('l M d, Y', strtotime($rate['Date'])); ?>
            </td>
            <td>
                1 <?php echo $this->baseCurrency->code ?> =
            </td>
            <td>
                <?php echo number_format($value,$decimal,$com1,$com2); ?>
            </td>
        </tr>
    <?php $count++; } ?>
</table>
    </div>
</div>

    </div>
<script>
jQuery(document).ready(function(){
	
	jQuery("#inputamount").focus(function() {
		jQuery("#inputamount").val('');
	});
jQuery('.validate').click(function(e){ 
        var inputamount = jQuery("#inputamount").val();
        if(inputamount == ''){e.preventDefault();jQuery("#inputamount").val("<?php echo JRequest::getVar('value'); ?>");}
        else return true;
    });
});
</script> 

   
  
