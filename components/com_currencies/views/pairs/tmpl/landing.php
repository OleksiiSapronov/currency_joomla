<?php
$doc = JFactory::getDocument(); 
//$doc->addScript(JUri::root() . 'components/com_currencies/assets/js/holder.min.js');
$this->range = array(1, 2, 5, 10, 15, 20, 25, 50);
$params = JComponentHelper::getParams('com_currencies');
$dropdown_currencies = $params->get('dropdown_currencies');
$dropdown_currencies = explode(',', $dropdown_currencies);
$currenciesModel = JModelLegacy::getInstance('currency', 'CurrenciesModel');
$top_currencies = $currenciesModel->getCurrenciesByCodes($dropdown_currencies);
$lastUpdateDate = $currenciesModel->getLastUpdateDate($this->base->code);
$countries = $currenciesModel->getCountriesByCurrencyCode($this->base->code);
$continents = $currenciesModel->getContinents();
$lastUpdateDate = gmdate('M d, Y H:i', strtotime($lastUpdateDate));
$gbase=$this->params->get('base_currency');
$glang=$this->params->get('component_lang');
$doc->addStyleSheet(JUri::root() . 'components/com_currencies/assets/css/flags.min.css');

$lang = JFactory::getLanguage(); 
$datelang = $lang->getTag();
$datelang = str_replace("-","_",$datelang).'.UTF-8';

$contents = explode(' ', $this->base->name);

$decimal = 2;
if ($glang == "pl") {    $com1 = ",";    $com2 = " ";}
if ($glang == "en") {    $com1 = ".";    $com2 = ",";}

if ($lang->getTag() == 'hi-IN' || $lang->getTag() == 'en-CA' || $lang->getTag() == 'en-NZ' || $lang->getTag() == 'en-AU') 
{$c=$cc=$all= new \NumberFormatter("te", \NumberFormatter::DECIMAL);}

elseif ($lang->getTag() == 'en-GB' || $lang->getTag() == 'th-TH'|| $lang->getTag() == 'ms-MY'|| $lang->getTag() == 'zh-TW'|| $lang->getTag() == 'he-IL'|| $lang->getTag() == 'ja-JP'|| $lang->getTag() == 'ko-KR '|| $lang->getTag() == 'zh-CN' )

{$c=$cc=$all= new \NumberFormatter("en-GB", \NumberFormatter::DECIMAL); }

elseif ($lang->getTag() == 'pt-BR')
{$c=$cc=$all= new \NumberFormatter("pt-BR", \NumberFormatter::DECIMAL); }

else
{$c=$cc=$all= new \NumberFormatter("fr-FR", \NumberFormatter::DECIMAL); }

$all->setAttribute(\NumberFormatter::FRACTION_DIGITS, countDecimals($this->value));


//JText::_('MAIN_'.strtoupper($this->base->name))

//require_once '';
require_once JPATH_COMPONENT . '/ftp/Mobile_Detect.php';
$detect = new Mobile_Detect;


$pairtitle =  sprintf (JText::_('CURRENCIES_6'),JText::_('MAIN_'.strtoupper($this->base->name)),$this->base->code,JText::_('MAINMC_'.strtoupper($this->base->name))) ;


$document = JFactory::getDocument();
$document->setTitle($pairtitle);

$pairkeywords = JText::_('MAIN_'.strtoupper($this->base->name)).", ".$this->base->name . JText::_('KEYWORDS1') . $this->base->code; //

$pairdescription = " (" . $this->base->code . ")" . " " . JText::_('MAIN_'.strtoupper($this->base->name)) .", ".$this->base->name. " " . JText::_('COM_CURRENCIES_CONVERT_D1');

$document->setMetaData("keywords", $pairkeywords);
$document->setMetaData("og:title", $pairtitle, 'property');
$document->setMetaData("description", $pairdescription, 'property');

if ($this->base->code == "BTC") {
	$decimal = 0;
}
?>
<div class="currenciesc container-fluid">
    <div class="col-xs-12 col-md-9 col-lg-9">
        <div class="row">

            <div class="col-sm-12" style="margin-bottom:10px">

                <h1> <?php printf(JText::_('COM_CURRENCIES_LANDING_PAGE_TITLE'), (JText::_('MAIN_'.strtoupper($this->base->name)) . " " . $this->base->code . "")) ?></h1>
	            <?php echo JText::_('COM_CURRENCIES_LAST_UPDATE'); ?><?php setlocale(LC_ALL, sprintf($datelang, $glang, strtoupper($glang))); echo strftime('%e %B %Y %H:%m', strtotime($lastUpdateDate)) . " UTC." ?>
            </div>
        </div>
        <div class="row flag-row">

            <div class="col-sm-4 date-col">

            </div>
			<?php for ($i = 0; $i < count($this->currencies); $i++) { ?>
				<?php $currency = $this->currencies[$i]; ?>
                <div class="col-md-1 col-sm-2 flag-col
    <?php if ($detect->isMobile() && !$detect->isTablet() && $i > 3) {
					echo 'hidden-sm hidden-xs';
				} ?>
    <?php if ($detect->isTablet() && $i > 7) {
					echo 'hidden-sm hidden-xs';
				} ?>
                 " title="<?php echo $currency->name ?>">
                    <a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->params->get('primary_currency') . '&to=' . $currency->code . '&value=' . $this->defaultValue . '&Itemid=' . $this->params->get('pairs_itemid')); ?>">

<img src="components/com_currencies/assets/images/blank.gif" class="flag flag-<?php echo strtolower(substr($currency->flag, 0, 2)); ?>" alt="<?php echo $currency->code." ".$currency->name?>" /> <?php echo $currency->code ?>
                    
					
					</a>
                </div>
			<?php } ?>
        </div>

        <div class="row rate-row">
            <div class="col-sm-2 base-flag">


<img src="components/com_currencies/assets/images/blank.gif" style="margin-top:-12px" class="flag flag-<?php echo strtolower(substr($this->base->flag, 0, 2)); ?>" alt="<?php echo $currency->code." ".$currency->name?>" /> 
				
                <br>

				<?php if (count($this->baseRate) > 0): ?>
					<?php if (floatval($this->baseRate[1]) >= 1000): ?>
                        <p class="waluta"><?php echo decima0(10000)." ". $this->base->code;?> = </p>
					<?php endif; ?>
					<?php if (floatval($this->baseRate[1])< 1000 && (floatval($this->baseRate[1])>= 100)): ?>
                        <p class="waluta"><?php echo decima0(1000)." ". $this->base->code;?> = </p>
					<?php endif; ?>
					<?php if (floatval($this->baseRate[1])< 100 && (floatval($this->baseRate[1])>= 10)): ?>
                        <p class="waluta"><?php echo decima0(100)." ". $this->base->code;?> = </p>
					<?php endif; ?>
					<?php if (floatval($this->baseRate[1])< 10): ?>
                        <p class="waluta">1 <?php echo $this->base->code ?> = </p>
					<?php endif; ?>
				<?php endif; ?>
                <!--<p>Inverse </p>-->
            </div>
            <?php
            $db = JFactory::getDbo();
            $sql = 'select * from #__currencies_rates_current';
            $db->setQuery($sql);
            $rates = $db->loadObjectList();
            $rates[] = (object) ["id" => "155", "base_currency" => "GBP", "currency2" => "GBP", "rate" => "0.9999734", "date" => "2024-06-18 01:54:25"];
            ?>
			<?php for ($i = 0; $i < count($this->currencies); $i++) { ?>

				<?php $currency = $this->currencies[$i]; ?>
				<?php
				$db = JFactory::getDBO();
				$sql = "SELECT currrency_symbol,currency_name FROM #__countries where currency_code = '$currency->code'";
				$db->setQuery($sql);
				$options = $db->loadObjectList();
				?>
				<?php
                # $this->base->code currency of page
                # $currency->code current currency
                $currentRate1 = 1; // currency of page
                $currentRate2 = 1; // current currency

				foreach ($rates as $rate)
				{
                    if ($rate->currency2 == $this->base->code)
                    {
                        $currentRate1 = $rate->rate;
                    }
                    elseif ($rate->currency2 == $currency->code)
                    {
	                    $currentRate2 = $rate->rate;
                    }
                }

				$rate = (float)$currentRate2 / (float)$currentRate1;



				$newRate = $rate;
				if (count($this->baseRate) > 0) {
					if (floatval($this->baseRate[1]) >= 1000) {
						$newRate = $rate * 10000;
					}
					else if (floatval($this->baseRate[1]) >= 100) {
						$newRate = $rate * 1000;
					}
					else if (floatval($this->baseRate[1]) >= 10) {
						$newRate = $rate * 100;
					}
					else {
						$newRate = $rate;
					}

				}


				?>
                <div class="col-md-1 col-sm-2 rate-col		   <?php if ($detect->isMobile() && !$detect->isTablet() && $i > 3) {
					echo 'hidden-sm hidden-xs';
				} ?>
                <?php if ($detect->isTablet() && $i > 7) {
					echo 'hidden-sm hidden-xs';
				} ?>
                 ">

					<?php

					
					if (trim($options[0]->currency_name) == 'Dollar') {
						?>

                        <p> <?php
if ($this->base->code=="BTC") {echo  decima0($newRate);}
else {echo $options[0]->currrency_symbol . decima2($newRate);}

 ?>

                        </p>
                        <!-- <p> <?php //echo  number_format($rates_reverse['Rate'],$decimal,$com1,$com2)  ?>
                    </p>-->
						<?php
					} else {
						?>
                        <p><?php 

if ($this->base->code=="BTC") {echo  decima0($newRate) ;}
else {echo decima2($newRate) . $options[0]->currrency_symbol;}
						
					 ?></p>
                        <!--<p><?php //echo number_format($rates_reverse['Rate'],$decimal,$com1,$com2);  ?></p>-->
					<?php }
					?>
                </div>
			<?php } ?>
        </div>
        <!-- converter -->
      
        <div class="row">
		
		{module Reklama big banner BOTTOM}
		
            <div class="col-sm-12">

			
			
                <form class="form-inline form-validate" action="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&task=rates.convert&Itemid=' . $this->params->get('pairs_itemid')) ?>" method="post">
                    <div class="form-group">

                        <label class="sr-only" for="inputamount"><?php echo JText::_('AMOUNT') ?></label>

                        <input type="number" step="0.000001"  value="1" name="value" class="form-control required" id="inputamount" placeholder="<?php echo JText::_('AMOUNT') ?>" lang="en-150">

                    </div>
                    <div class="form-group">

                        <label class="sr-only" for="currencyselect_fromd">From</label>

                        <select class="required input-large" name="base" id="currencyselect_fromd">
                            <optgroup label="<?php echo JText::_('COM_CURRENCIES_RECENT')?>">
								<?php foreach ($top_currencies as $top_currency): ?>
                                    <option <?php echo $top_currency->code == $this->base->code ? 'selected="selected"' : '' ?> value="<?php echo $top_currency->code ?>" data-image="<?php echo Juri::root() . 'images/flags/' . $top_currency->flag ?>">
										<?php echo $top_currency->code ?> - <?php echo JText::_('MAIN_'.strtoupper($top_currency->name)) ?>
                                    </option>
								<?php endforeach; ?>
                            </optgroup>

                            <optgroup label="<?php echo JText::_('COM_CURRENCIES_ALLCURR')?>">
								<?php foreach ($this->allCurrencies as $cfrom) { ?>
                                    <option <?php echo $cfrom->code == $this->base->code ? 'selected="selected"' : '' ?> value="<?php echo $cfrom->code ?>" data-image="<?php echo Juri::root() . 'images/flags/' . $cfrom->flag ?>">
										<?php echo $cfrom->code ?> - <?php echo JText::_('MAIN_'.strtoupper($cfrom->name)) ?>
                                    </option>
								<?php } ?>
                            </optgroup>
                        </select>

                    </div>

                    <div class="form-group">

                        <label class="sr-only" for="convertto">To</label>

                        <select name="to" class="required input-large" id="currencyselect_tod">
                            <optgroup label="<?php echo JText::_('COM_CURRENCIES_RECENT')?>">
								<?php foreach ($top_currencies as $top_currency): ?>
                                    <option <?php echo $top_currency->code == $this->defaultCurrency->code ? 'selected="selected"' : '' ?> value="<?php echo $top_currency->code ?>" data-image="<?php echo Juri::root() . 'images/flags/' . $top_currency->flag ?>">
										<?php echo $top_currency->code ?> - <?php echo JText::_('MAIN_'.strtoupper($top_currency->name)) ?>
                                    </option>
								<?php endforeach; ?>
                            </optgroup>

                            <optgroup label="<?php echo JText::_('COM_CURRENCIES_ALLCURR')?>">
								<?php foreach ($this->allCurrencies as $cto) { ?>
                                    <option  <?php echo $cto->code == $this->defaultCurrency->code ? 'selected="selected"' : '' ?>  value="<?php echo $cto->code ?>" data-imagesrc="<?php echo Juri::root() . 'images/flags/' . $cto->flag ?>"
                                                                                                                                    data-description="<?php echo $cto->name ?>"><?php echo $cto->code ?> - <?php echo JText::_('MAIN_'.strtoupper($cto->name)) ?></option>
								<?php } ?>
                            </optgroup>
                        </select>

                    </div>

                    <button type="submit" class="btn btn-primary validate"><?php echo JText::_('COM_CURRENCIES_CONVERT')?></button>
                    <input type="hidden" value="com_currencies" name="option">
                    <input type="hidden" value="pairs" name="view">
                    <input type="hidden" value="rates.convert" name="task">


                </form>
            </div>

		{module Reklama MOBILE}
{module Reklama MOBILELINK}
			
        </div>
		

        <!-- end converter -->
        <div  class="col-sm-12 well center shandow">

		
			<?php

			if ($this->base->code=="GBP") {$funt="Pound";} else {$funt= end($contents);	}
			if ($this->defaultCurrency->code=="TRY") {$lira="TL";} else {$lira="";}
			echo sprintf (JText::_('CURRENCIES_7A'),$funt,JText::_('MAIN_'.strtoupper($this->defaultCurrency->name)),$lira,JText::_('MAINMC_'.strtoupper($this->defaultCurrency->name)),JText::_('MAINMC_'.strtoupper($this->base->name)) );

			
			
			
			?>

<?php
if ((	 $this->current[1]*$this->value)<0.1) {$decimalx=decima7($this->current[1]*$this->value);}
elseif (($this->current[1]*$this->value)<1) {$decimalx=decima5($this->current[1]*$this->value);}
elseif (($this->current[1]*$this->value)<10) {$decimalx=decima4($this->current[1]*$this->value);}
elseif (($this->current[1]*$this->value)<100) {$decimalx=decima3($this->current[1]*$this->value);}
elseif (($this->current[1]*$this->value)<1000) {$decimalx=decima2($this->current[1]*$this->value);}
elseif (($this->current[1]*$this->value)<10000) {$decimalx=decima1($this->current[1]*$this->value);}
else {$decimalx=decima0($this->current[1]*$this->value);}

if 		($this->current[1]<0.1) {$decimalx1=decima7($this->current[1]);}
elseif ($this->current[1]<1) {$decimalx1=decima5($this->current[1]);}
elseif ($this->current[1]<10) {$decimalx1=decima4($this->current[1]);}
elseif ($this->current[1]<100) {$decimalx1=decima3($this->current[1]);}
elseif ($this->current[1]<1000) {$decimalx1=decima2($this->current[1]);}
elseif ($this->current[1]<10000) {$decimalx1=decima1($this->current[1]);}
else {$decimalx1=decima0($this->current[1]);}

if 	   (( $this->inverse[1])<0.1) {$decimalx2=decima7($this->inverse[1]);}
elseif (($this->inverse[1])<1) {$decimalx2=decima5($this->inverse[1]);}
elseif (($this->inverse[1])<10) {$decimalx2=decima4($this->inverse[1]);}
elseif (($this->inverse[1])<100) {$decimalx2=decima3($this->inverse[1]);}
elseif (($this->inverse[1])<1000) {$decimalx2=decima2($this->inverse[1]);}
elseif (($this->inverse[1])<10000) {$decimalx2=decima1($this->inverse[1]);}
else {$decimalx2=decima0($this->inverse[1]);}



?>

   <h2 style="color:#F96010;" >  <?php echo number_format(($this->value),0). " "; echo $this->base->code . " = ".   $decimalx.' '.$this->defaultCurrency->code ?></h2>

            <table width="100%" cellspacing="5" cellpadding="5" align="center" class="praweokno">
                <tbody>
                <tr>
                    <td class="currencytd">
                        <a href="<?php echo $this->base->page ?>">  <?php echo JText::_('COM_CURRENCIES_CHECK_THIS_CURRENCY')."<br>". JText::_('MAIN_'.strtoupper($this->base->name)) ?> </a>
                        <p class="currencyone"> 1 <?php echo JText::_('MAIN_'.strtoupper($this->base->name)) ?> (<?php echo $this->base->symbol ?>) = <?php echo $decimalx1 ?> <?php echo $this->defaultCurrency->currency_name ?> (<?php echo $this->defaultCurrency->symbol ?>)</p>
                    </td>

                    <td class="currencytd">
                        <a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->defaultCurrency->code . '&layout=landing&Itemid=' . $this->params->get('pairs_itemid')) ?>"> <?php echo JText::_('COM_CURRENCIES_CHECK_THIS_CURRENCY')."<br>". JText::_('MAIN_'.strtoupper($this->defaultCurrency->name)) ?> </a>
                        <p class="currencyone"> 1 <?php echo JText::_('MAIN_'.strtoupper($this->defaultCurrency->name)) ?>  (<?php echo $this->defaultCurrency->symbol?> ) = <?php  echo  $decimalx2?>
							<?php echo " ". JText::_('MAIN_'.strtoupper($this->base->name)) ?>  (<?php echo $this->base->symbol?>)</p>

                    </td>
                </tr>
                <tr>
                    <td class="currency" colspan="3">
                    </td>
                </tr>
                <tr>
                    <td class="currency" colspan=3">

                    </td>

                </tr>



                <!--<tr>


				<td class="currencyname" style="width: 150px;text-align: right;">

					<a href="<?php // echo JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->base->code . '&layout=landing&Itemid=' . $this->params->get('pairs_itemid')) ?>"> <?php // printf(JText::_('COM_CURRENCIES_MORE_ABOUT_CURRENCY'),JText::_('MAIN_'.strtoupper($this->base->name))) ?></a>				</td>
				<td></td>
				<td class="currencyname" style="width: 150px;text-align: left">
				<a href="<?php // echo  $this->currency2->article ?>"> <?php //printf(JText::_('COM_CURRENCIES_MORE_ABOUT_CURRENCY'),$this->currency2->name) ?></a>				</td>

			</tr>-->
                <tr>
                    <td colspan="3">


						<?php

						$link1 =  '<a href="'.JRoute::_('index.php?option=com_currencies&view=pairs&base='.$this->base->code.'&layout=calculator&Itemid='.$this->params->get('pairs_itemid')).'">'.  $this->base->code." ".JText::_('COM_CURRENCIES_SMCALCULATOR').'</a>';

						$link2 =  '<a href="'.JRoute::_('index.php?option=com_currencies&view=pairs&base='.$this->defaultCurrency->code.'&layout=calculator&Itemid='.$this->params->get('pairs_itemid')).'">'.  $this->defaultCurrency->code." ".JText::_('COM_CURRENCIES_SMCALCULATOR').'</a>';

						echo sprintf(JText::_('CURRENCIES_2'),"<strong>","</strong>",number_format(($this->value),0,$com1,$com2),JText::_('MAIN_'.strtoupper($this->base->name)),$this->base->code,JText::_('MAIN_'.strtoupper($this->defaultCurrency->name)),$this->defaultCurrency->code,date('Y-m-d H:i:s',strtotime($lastUpdateDate)),$link1,$link2,$this->base->name,JText::_('MAINMC_'.strtoupper($this->base->name)))


						;?>





                    </td>
                </tr>



                </tbody></table>
        </div>

        <br>
        <div class="row">
		
			{module Reklama MOBILE}
{module Reklama MOBILELINK}
		
            <div class="col-sm-12">

                <!-- Tabs start -->
                <h2><?php echo JText::_('MAJOR_WORLD_CURRENCIES')?> </h2>
                <ul class="nav nav-tabs">
                    <li class="active"><a data-toggle="tab" id="popular" href="#popular"><?php echo JText::_('MAIN_CURRENCIES');?></a></li>
					<?php for ($c = 0; $c < count($continents); $c++) {
						if ($continents[$c] != '') { ?>
                            <li><a data-toggle="tab" id="<?php echo strtolower($continents[$c]); ?>" href="#<?php echo strtolower($continents[$c]); ?>"><?php echo JText::_('MAIN_LA'.strtoupper(ucfirst($continents[$c]))); ?></a></li>




						<?php }
					} ?>
                </ul>
				
                <table class="table table-striped table-bordered curr-table">
                    <tr class="show">
                        <th colspan="5">
							<?php

							echo JText::sprintf('CURRENCIES_1',JText::_('MAIN_'.strtoupper($this->base->name))." (".$this->base->code.")" ,date('Y-m-d H:i:s', strtotime($lastUpdateDate)))

								//echo JText::sprintf('COM_CURRENCIES_EXCHANGE_RATES', JText::_('MAIN_'.strtoupper($this->base->name)), JText::_('MAIN_'.strtoupper($this->currency2->name)))

							; ?>
                            <br>
							
							
                            <span class="tabelkacurr">

<?php echo sprintf(JText::_('CURRENCIES_5'),JText::_('MAIN_'.strtoupper($this->base->name)),"<strong>","</strong>",$this->base->name); ?>

</span>

                        </th>


                        <!--	<div class="reklama"> </div> <br> -->


                    </tr>


                    <tr class="show">
                        <th class="firstColumn"><?php echo JText::_('COM_CURRENCIES_CURRENCY') ?></th>
                        <th><?php echo JText::_('COM_CURRENCIES_EXCHANGE') ?></th>
                        <th class="hidden-sm hidden-xs"> <?php echo JText::_('COM_CURRENCIES_CALCULATOR') ?></th>
                        <th> <?php echo JText::_('COM_CURRENCIES_PAIRING') ?></th>
                        <!--<th  class="hidden-sm hidden-xs"> <?php echo JText::_('COM_CURRENCIES_HISTORICAL_EXCHANGE') ?></th>-->
                    
					
					
					
					</tr>
					
					
					<?php
					$count = 0;

					foreach ($this->historyRates as $hrate) {
						if ($hrate->country == "United Kingdom") {
							$hrate = (object) ["id" => "155", "base_currency" => "GBP", "currency2" => "GBP", "rate" => "0.9999734", "date" 																		=> "2024-06-18 01:54:25", "Rate" => "0.9999734", "Date" => "2024-06-18 01:54:25", "flag" => "GB.png", "country" => "United Kingdom", "currency_name" => "British Pound Sterling", "main" => "1", "continent" => "Europe"];
						}

						$os1 = array("VND", "IDR", "GNF", "KHR", "IRR", "PYG", "BYR", "COP", "UZS", "MGA", "LAK", "MNT", "KPW","STD","ZMK","ZWL");
						$os2 = array("UGX", "XOF", "LBP", "KMF", "CRC", "LBP", "BIF", "TZS", "RWF", "IQD", "MMK", "SYP", "CLP", "KRW", "AMD","VUV","BTN","AOA","MWK","SLL","SOS");
						$os3 = array("ALL", "CVE", "AFN", "LKR", "ISK", "HUF", "NGN", "DZD", "KES", "KZT", "THB", "TWD", "JPY", "RUB", "HNL", "MZN", "NPR", "DJF", "INR", "PKR", "MKD", "RSD", "UYU", "BDT", "YER", "PHP", "JMD", "DOP", "GYD", "HTG", "HKD", "SBD", "KGS", "XAF","XPF","GMD","LSL","LRD","MRO","MUR","SCR","SZL","CUP");
						$os4 = array("MOP", "BWP", "ARS", "ZAR", "ETB", "zzz", "NOK", "BOB", "ERN", "GTQ", "MXN", "SEK", "NIO", "PEN", "EGP", "MDL", "QAR", "UAH", "MUR", "SAR", "MAD", "GEL", "AED", "VEF", "HRK", "NAD", "DKK", "ZMK", "PLN", "CNY", "BRL", "TTD", "MYR", "CZK", "ILS", "LTL", "TRY","SVC","SRD","BAM","RON","LYD","QAR","SAR","TJS","TMT","WST");

						if (in_array($this->base->code, $os1)) {
							$decimal = decima7($hrate->rate / $currentRate1);
						}
						elseif (in_array($this->base->code, $os2)) {
							$decimal = decima7($hrate->rate / $currentRate1);
						}
						elseif (in_array($this->base->code, $os3)) {
							$decimal = decima6($hrate->rate / $currentRate1);
						}
						elseif (in_array($this->base->code, $os4)) {
							$decimal = decima5($hrate->rate / $currentRate1);
						}
						elseif ($this->base->code == "BTC") {
							$decimal = decima0($hrate->rate / $currentRate1);
						}
						else {$decimal = decima4($hrate->rate / $currentRate1);}

						$rowClass = ($count % 2 == 0) ? "even" : "odd";
						if (empty($hrate->flag))
							continue;
						?>
						
						
						
                        <tr class="<?php echo $rowClass ?> <?php echo strtolower($hrate->continent); ?> <?php if ($hrate->main == 1) echo 'popular'; ?>">
                        

						<td class="country firstColumn">

   
   <img src="components/com_currencies/assets/images/blank.gif" class="flag flag-<?php echo strtolower(substr($hrate->flag, 0, 2)); ?>" alt="<?php echo $hrate->currency_name?>" /> 
   
   <a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $hrate->currency2 . '&layout=landing&Itemid=' . $this->params->get('pairs_itemid')) ?>"><?php echo  JText::_(JText::_('MAIN_' . strtoupper($hrate->currency_name))) . ' (' . $hrate->currency2 . ')' ?></a>
   
   
   
                            </td>
                            <td class="currencyex">
                                
<a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->base->code . '&to=' . $hrate->currency2 . '&value=1') ?>"> <?php echo "1 ";  echo $this->base->code." = ". $decimal." " .$hrate->currency2 ?></a>
							



                            </td>
                            <td class="hidden-sm hidden-xs">

                                <a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base='.$hrate->currency2.'&layout=calculator&Itemid='.$this->params->get('pairs_itemid')) ?>"><?php echo $hrate->currency2." ".JText::_('COM_CURRENCIES_SMCALCULATOR');?></a>

                            </td>
                            <td class="pairingex">
                                <a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->base->code . '&to=' . $hrate->currency2 . '&value=10') ?>"> <?php echo JText::_('COM_CURRENCIES_CONVERT_T')." " . $this->base->code ." ". JText::_('COM_CURRENCIES_ON_THIS_TO'). " " . $hrate->currency2 ?></a>
                            </td>






                            <!--                        <td  class="hidden-sm hidden-xs">
                            <a href="<?php echo JRoute::_('index.php?option=com_currencies&view=history&base=' . $this->base->code . '&to=' . $hrate->currency2) ?>"><?php echo JText::_('COM_CURRENCIES_HISTORICAL') ?></a>
                        </td>-->

                        </tr>
						<?php $count++;
					} ?>
                </table>

            </div>
        </div>
{module Reklama big banner BOTTOM} <br>
        <div class="countries">
			<?php
			$con = '';$cCount=1;
			$countries = explode(', ', $countries);
			$total = count($countries);
			foreach($countries as $country){
				$con .= " " . JText::_('MAIN_'. strtoupper($country));
				if($cCount != $total){$con .=', ';}
				$cCount++;
			}

		
echo sprintf (JText::_('CURRENCIES_3'),JText::_('MAIN_'.strtoupper($this->base->name)),$this->base->code,$con) ; ?>			
			

        </div>	<br>
        <div class="countries">

			<?php echo sprintf (JText::_('CURRENCIES_4'),"<strong>","</strong>",JText::_('MAIN_'.strtoupper($this->base->name)),$this->base->code) ; ?>



            <textarea rows="6" cols="20" class="textareafoot">&lt;a href="http://<?php echo $_SERVER['SERVER_NAME'] . JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->base->code . '&layout=landing&Itemid=' . $this->params->get('pairs_itemid')) ?>"&gt;<?php echo JText::_('MAIN_'.strtoupper($this->base->name)) . " (" . $this->base->code . ")" . " " ?> <?php echo JText::_('COM_CURRENCIES_EXCHANGE_RATESFOO') ?>&lt;/a&gt;</textarea>
        </div>


    </div>
    <div class="col-xs-12 col-md-3 col-lg-3 side-modules">
        <br>
            <br>{module module="Currency List" showtitle="false"}
   <?php //  {module module="Last queries" showtitle="true" title="<?php // echo JText::_('LASTQ').' "}'?>
   <?php // echo '{module module="Last 24 hour" showtitle="true"}'; ?>
    </div>

</div>

<script>
    jQuery(document).ready(function () {

        jQuery("#inputamount").focus(function() {
            jQuery("#inputamount").val('');
        });
        jQuery('.validate').click(function(e){
            var inputamount = jQuery("#inputamount").val();
            if(inputamount == ''){e.preventDefault();jQuery("#inputamount").val("<?php echo JRequest::getVar('value'); ?>");}
            else return true;
        });


        jQuery('.curr-table tr').hide();
        jQuery('.popular').show();
        jQuery('.nav.nav-tabs a').click(function () {
            var a_id = jQuery(this).attr('id');
            jQuery('.curr-table tr').hide();
            jQuery('.' + a_id).show();
        });
    });
</script>
<style>
    .show{display: table-row !important; }
</style>

