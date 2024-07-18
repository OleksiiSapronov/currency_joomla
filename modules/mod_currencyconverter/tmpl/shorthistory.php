<?php $currencyConverterHelper  = new CurrencyConverterHelper(); ?>
<?php if(!empty($dataFrom)){ ?>
<div class="panel-heading">
<h3 class="panel-title"><?php echo  $dataFrom['title']?> exchange rates</h3>
</div>
<table class="flightpedia table">
		<tbody>
		<tr>
			<th>Name</th>
			<th>Rate</th>
                        <th>Yesterday</th>
			<th>Percent</th>
		</tr>
		<?php 
		foreach($dataFrom['stats'] as $rate){
			
		//$targetCurrency = str_ireplace($currencyFrom,'',$rate['@attributes']['id']);
//		$targetCurrency = $rate->base_currency;
		$baseCurrency = $rate->base_currency;
		$targetCurrency = $rate->currency2;
//		$change  =$ratesmodel->getChangeRate($rate->rate,$currencyFrom, $targetCurrency);
		
		$change  = $currencyConverterHelper->getChangeRate($rate->rate,$baseCurrency, $targetCurrency);
		?>
		<tr>
                    <td><a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base='.$baseCurrency.'&layout=calculator&Itemid=158');?>"><?php echo $baseCurrency . ' - ' . $targetCurrency ?></a></td>
			<td><?php echo number_format($rate->rate,$componentParams->get('decimal_places',2))?></td>
			<td><?php echo number_format($change->yesterday,$componentParams->get('decimal_places',2)); ?></td>
                        <td>
				<i class="<?php echo $change->percent >0?'fa fa-sort-asc fa-2 exup':'fa fa-sort-desc fa-2 exdown' ?>">&nbsp;</i> <?php echo abs($change->percent) ?>% 
			</td>
		</tr>
		<?php } ?>
	     </tbody>
	     </table>
													
													<?php } ?>
													
<?php if(isset($dataTo) && count($dataTo)){?>
													<div class="panel-heading">
<h3 class="panel-title"><?php echo $dataTo['title'] ?> exchange rates</h3>
</div>
		<table class="flightpedia table">
		<tbody><tr>
			<th>Name</th>
			<th>Rate</th>
			<th>Yesterday</th>
			<th>Change</th>
		</tr>
		<?php 
		foreach($dataTo['stats'] as $rate){
			
		//$targetCurrency = str_ireplace($currencyTo,'',$rate['@attributes']['id']);
//		$targetCurrency = $rate->base_currency;
		$baseCurrency = $rate->base_currency;
		$targetCurrency = $rate->currency2;
//		$change  =$ratesmodel->getChangeRate($rate->rate,$currencyFrom, $targetCurrency);
                $change  = $currencyConverterHelper->getChangeRate($rate->rate,$baseCurrency, $targetCurrency);
//                echo $rate->rate.' '.$change;
                
		?>
		<tr>
                    <td><a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base='.$baseCurrency.'&layout=calculator&Itemid=158');?>"><?php echo $baseCurrency . ' - ' . $targetCurrency ?></a></td>
			<td><?php echo number_format($rate->rate,$componentParams->get('decimal_places',2))?></td>
			<td><?php echo number_format($change->yesterday,$componentParams->get('decimal_places',2)); ?></td>
			<td>
				<i class="<?php echo $change->percent >0?'fa fa-sort-asc fa-2 exup':'fa fa-sort-desc fa-2 exdown' ?>">&nbsp;</i>  <?php echo abs($change->percent ) ?>% 
			</td>
		</tr>
		<?php } ?>
		</tbody>
		</table>
<?php } ?>
									