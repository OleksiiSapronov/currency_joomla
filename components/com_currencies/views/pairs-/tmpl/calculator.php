<?php
$doc = JFactory::getDocument();
//$doc->addScript(JUri::root() . 'components/com_currencies/assets/js/holder.min.js');
$this->range         = array(1, 2, 5, 10, 15, 20, 25, 50);
$params              = JComponentHelper::getParams('com_currencies');
$dropdown_currencies = $params->get('dropdown_currencies');
$dropdown_currencies = explode(',', $dropdown_currencies);
$currenciesModel     = JModelLegacy::getInstance('currency', 'CurrenciesModel');
$top_currencies      = $currenciesModel->getCurrenciesByCodes($dropdown_currencies);
$countries           = $currenciesModel->getCountriesByCurrencyCode($this->base->code);
$continents          = $currenciesModel->getContinents();
$lastUpdateDate      = gmdate('M d, Y H:i', strtotime($currenciesModel->getLastUpdateDate()));
$amount              = JFactory::getApplication()->input->get('value', 100);
$gbase               = $this->params->get('base_currency');
$glang               = $this->params->get('component_lang');
$doc->addStyleSheet(JUri::root() . 'components/com_currencies/assets/css/flags.min.css');
$lang = JFactory::getLanguage();
$datelang = $lang->getTag();
$datelang = str_replace("-","_",$datelang).'.UTF-8';
	
if ($lang->getTag() == 'hi-IN' || $lang->getTag() == 'en-CA' || $lang->getTag() == 'en-NZ' || $lang->getTag() == 'en-AU') 
{$c=$cc=$all= new \NumberFormatter("te", \NumberFormatter::DECIMAL);}

elseif ($lang->getTag() == 'en-GB' || $lang->getTag() == 'th-TH'|| $lang->getTag() == 'ms-MY'|| $lang->getTag() == 'zh-TW'|| $lang->getTag() == 'he-IL'|| $lang->getTag() == 'ja-JP'|| $lang->getTag() == 'ko-KR '|| $lang->getTag() == 'zh-CN' )

{$c=$cc=$all= new \NumberFormatter("en-GB", \NumberFormatter::DECIMAL); }

elseif ($lang->getTag() == 'pt-BR')
{$c=$cc=$all= new \NumberFormatter("pt-BR", \NumberFormatter::DECIMAL); }

else
{$c=$cc=$all= new \NumberFormatter("fr-FR", \NumberFormatter::DECIMAL); }

$all->setAttribute(\NumberFormatter::FRACTION_DIGITS, countDecimals($this->value));
	
	
	
$contents = explode(' ', $this->base->name);
if ($this->base->code == "GBP")
{
	$funt = "Pound";
}
else
{
	$funt = end($contents);
}

$decimal = 2;
if ($glang == "pl")
{ 	$com1 = ","; 	$com2 = " "; }
if ($glang == "en")
{ 	$com1 = "."; 	$com2 = ","; }
if ($amount < 1)
{	$amount = 100;}

$decimal = 2;
$lang1   = "pl";
if ($lang1 == "pl")
{
	$com1 = ",";
	$com2 = " ";
}
if ($lang1 == "en")
{
	$com1 = ".";
	$com2 = ",";
}

//JText::_('MAIN_'.strtoupper($this->base->name))

//require_once 'ftp/Mobile_Detect.php';
require_once JPATH_COMPONENT . '/ftp/Mobile_Detect.php';
$detect = new Mobile_Detect;

$pairtitle = sprintf(JText::_('CURRENCIES_12'), JText::_('MAIN_' . strtoupper($this->base->name)), $this->base->code, "", "", "", "", "");


$document = JFactory::getDocument();
$document->setTitle($pairtitle);

$pairkeywords = JText::_('MAIN_' . strtoupper($this->base->name)) . ", " . $this->base->name . JText::_('KEYWORDS1') . $this->base->code . ", " . JText::_('COM_CURRENCIES_SMCALCULATOR'); //

$pairdescription = " (" . $this->base->code . ")" . " " . JText::_('MAIN_'.strtoupper($this->base->name)) .", " . sprintf (JText::_('COM_CURRENCIES_CONVERT_D1'),$this->base->name,"","");

$document->setMetaData("keywords", $pairkeywords);
$document->setMetaData("og:title", $pairtitle, 'property');
$document->setMetaData("description", $pairdescription, 'property');

if ($this->base->code == "BTC")
{
	$decimal = 0;
}
?>


<div class="currenciesc container-fluid">
    <div class="row">
        <div class="col-sm-12" style="margin-bottom:10px">

            <h1> <?php echo sprintf(JText::_('CURRENCIES_11'), JText::_('MAINMC_' . strtoupper($this->base->name)), $this->base->code, "", "") ?></h1>

<?php echo JText::_('COM_CURRENCIES_LAST_UPDATE'); ?><?php setlocale(LC_ALL, sprintf($datelang, $glang, strtoupper($glang))); echo strftime('%e %b %Y %H:%m', strtotime($lastUpdateDate)) . " UTC." ?>
        
<?php 	   ?>
		
		</div>
    </div>
    <div class="row flag-row">

        <div class="col-sm-4 date-col">

        </div>
		<?php for ($i = 0; $i < count($this->currencies); $i++) { ?>
			<?php $currency = $this->currencies[$i]; ?>
            <div class="col-md-1 col-sm-2 flag-col 
    <?php if ($detect->isMobile() && !$detect->isTablet() && $i > 3)
			{
				echo 'hidden-sm hidden-xs';
			} ?>
    <?php if ($detect->isTablet() && $i > 7)
			{
				echo 'hidden-sm hidden-xs';
			} ?>
                 " title="<?php echo $currency->name ?>">
                <a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->params->get('primary_currency') . '&to=' . $currency->code . '&value=250&Itemid=' . $this->params->get('pairs_itemid')); ?>">

<img src="components/com_currencies/assets/images/blank.gif" class="flag flag-<?php echo strtolower(substr($currency->flag, 0, 2)); ?>" alt="<?php echo $currency->code." ".$currency->name?>" /> <?php echo $currency->code ?>


                </a>
				

            </div>
		<?php } ?>
    </div>

    <div class="row rate-row">
        <div class="col-sm-2 base-flag">


<img src="components/com_currencies/assets/images/blank.gif" style="margin-top:-12px" class="flag flag-<?php echo strtolower(substr($this->base->flag, 0, 2)); ?>" alt="<?php echo $this->base->code." ".$this->base->name?>" /> 			
			
			

            <br>

			<?php if (count($this->baseRate) > 0): ?>
				<?php if (floatval($this->baseRate[1]) >= 1000): ?>
                    <p class="waluta"><?php echo number_format(10000,0,$com1,$com2)." ". $this->base->code;?> = </p>
				<?php endif; ?>
				<?php if (floatval($this->baseRate[1])< 1000 && (floatval($this->baseRate[1])>= 100)): ?>
                    <p class="waluta"><?php echo number_format(1000,0,$com1,$com2)." ". $this->base->code;?> = </p>
				<?php endif; ?>
				<?php if (floatval($this->baseRate[1])< 100 && (floatval($this->baseRate[1])>= 10)): ?>
                    <p class="waluta"><?php echo number_format(100,0,$com1,$com2)." ". $this->base->code;?> = </p>
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
			$currentRate1 = 0; // currency of page
			$currentRate2 = 0; // current currency

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

			if ($this->base->code == "GBP") {$rate = (float)$currentRate2; }
	
			else {$rate = (float)$currentRate2 / (float)$currentRate1; }



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

			/* 	   if(floatval($this->baseRate[1]) >= 1000)
			  {
			  $newRate = $rate * 1000;
			  }
			  if(floatval($this->baseRate[1]) >= 10000)
			  {
			  $newRate = $rate * 10000;
			  } */
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

                    <p> <?php echo $options[0]->currrency_symbol . decima2($newRate) ?>
                    </p>
                    <!-- <p> <?php //echo  number_format($rates_reverse['Rate'],$decimal,$com1,$com2)  ?>
                    </p>-->
					<?php
				} else {
					?>
                    <p><?php echo decima2($newRate) . $options[0]->currrency_symbol ?></p>
                    <!--<p><?php //echo number_format($rates_reverse['Rate'],$decimal,$com1,$com2);  ?></p>-->
				<?php }
				?>
            </div>
		<?php } ?>
    </div>
    <!-- converter -->
    <br>
    <div class="row">
        <form class="form-inline form-validate"
              action="<?php echo JRoute::_('index.php?task=rates.updateURI'); ?>">
            <input type="hidden" value="com_currencies" name="option">
            <input type="hidden" value="pairs" name="view">
            <input type="hidden" value="calculator" name="layout">
            <input type="hidden" value="rates.updateURI" name="task">

            <div class="col-md-3">
                <label class="sr-only" for="inputamount"><?php echo JText::_('AMOUNT') ?></label>
                <input type="number" step="0.000001" value="<?php echo $amount; ?>" name="value"
                       class="form-control required" id="inputamount" placeholder="<?php echo JText::_('AMOUNT') ?>" lang="en-150">
            </div>

            <!--<div class="col-md-3"><h2 style="margin-top: 0px;"><?php echo JText::_('MAIN_' . strtoupper($this->base->name)) . " (" . $this->base->code . ")"; ?></h2></div>-->
            <div class="col-md-3">
                <select class="required input-large" name="base" id="currencyselect_fromd">
                    <optgroup label="<?php echo JText::_('COM_CURRENCIES_RECENT') ?>">
						<?php foreach ($top_currencies as $top_currency): ?>
                            <option <?php echo $top_currency->code == $this->base->code ? 'selected="selected"' : '' ?>
                                    value="<?php echo $top_currency->code ?>"
                                    data-image="
<?php echo Juri::root() . 'images/flags/' . $top_currency->flag ?>">





								<?php echo $top_currency->code ?>
                                - <?php echo JText::_('MAIN_' . strtoupper($top_currency->name)) ?>
                            </option>
						<?php endforeach; ?>
                    </optgroup>

                    <optgroup label="<?php echo JText::_('COM_CURRENCIES_ALLCURR') ?>">
						<?php foreach ($this->allCurrencies as $cfrom) { ?>
                            <option <?php echo $cfrom->code == $this->base->code ? 'selected="selected"' : '' ?>
                                    value="<?php echo $cfrom->code ?>"
                                    data-image="<?php echo Juri::root() . 'images/flags/' . $cfrom->flag ?>">
								<?php echo $cfrom->code ?> - <?php echo JText::_('MAIN_' . strtoupper($cfrom->name)) ?>
                            </option>
						<?php } ?>
                    </optgroup>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit"
                        class="btn btn-primary validate"><?php echo JText::_('COM_CURRENCIES_CONVERT') ?></button>
            </div>
        </form>
    </div>
    <!-- end converter -->
    <br>
    <div class="row">
        <div class="col-sm-12">

            <table class="table table-striped table-bordered curr-table">
                <tr class="show">
                    <th colspan="5">
						<?php echo sprintf(JText::_('CURRENCIES_8'), JText::_('MAIN_' . strtoupper($this->base->name)), $funt); ?>

                        <br>
                        <span class="tabelkacurr"> 

<?php 

$link1 =  '<a href="'.JRoute::_('index.php?option=com_currencies&view=pairs&base='.$this->base->code.'&to='.$this->defaultCurrency->code.'&value='.$this->value.'&Itemid='.$this->params->get('pairs_itemid')).'">'.JText::_('CURRENCIES_9A')." ". JText::_('MAIN_'.strtoupper($this->base->name)).'</a>' ;

$link2 =  '<a href="'.JRoute::_('index.php?option=com_currencies&view=pairs&base='.$this->base->code.'&layout=landing&Itemid='.$this->params->get('pairs_itemid')).'">'.JText::_('CURRENCIES_9B')." ". JText::_('MAIN_'.strtoupper($this->base->name)).'</a>' ;?>

<?php
echo sprintf (JText::_('CURRENCIES_9'),"<strong>","</strong>",$all->format($this->value),JText::_('MAIN_'.strtoupper($this->base->name)),date('Y-m-d H:i', strtotime($lastUpdateDate)),$link1,$link2,JText::_('MAINMC_'.strtoupper($this->base->name)));


?>


                    </th>
                </tr>

                <tr class="show">
                    <th><?php echo JText::_('COM_CURRENCIES_CURRENCY') ?></th>
                    <th><?php echo JText::_('COM_CURRENCIES_EXCHANGE') ?></th>
                    <th class="hidden-sm hidden-xs"> <?php echo JText::_('COM_CURRENCIES_LAST_UPDATE') ?></th>
                    <th> <?php echo JText::_('COM_CURRENCIES_PAIRING') ?></th>
                    <th class="hidden-sm hidden-xs"> <?php echo JText::_('COM_CURRENCIES_CALCULATOR') ?></th>
                </tr>
				<?php
				$count            = 0;
				$continents       = array();
				$historyRates     = $this->historyRates;
				$history_main     = array_filter($historyRates, function ($obj) {
					if (isset($obj))
					{

						if ($obj->main == 0) return false;

					}

					return true;
				});
				$history_rest     = array_filter($historyRates, function ($obj) {
					if (isset($obj))
					{

						if ($obj->main == 1) return false;

					}

					return true;
				});
				$history_rest_arr = array();
				$continentList    = array('Asia', 'Africa', 'Europe', 'MEA', 'America', 'Pacific');
				$z                = 0;
				foreach ($continentList as $continent)
				{
					${'history_rest_' . $z} = array_filter($history_rest, function ($obj) use ($continent) {
						if (isset($obj))
						{

							if ($obj->continent == $continent) return true;

						}

						return false;
					});
					$history_rest_arr       = array_merge($history_rest_arr, ${'history_rest_' . $z});
					$z++;
				}


				$historyRatesNew = array_merge($history_main, $history_rest_arr);
				echo '<tr><td colspan="5"><h4>';
				echo JText::_('MAJOR_WORLD_CURRENCIES');
				'</h4></td></tr>';
				foreach ($historyRatesNew as $hrate)
				{
    if ($this->base->code != $hrate->currency2) {
/*$os1 = array("VND", "IDR", "GNF", "KHR", "IRR", "PYG", "BYR", "COP", "UZS", "MGA", "xxx", "xxx", "xxx");
					    $os2 = array("UGX", "XOF", "LBP", "KMF", "CRC", "LBP", "BIF", "TZS", "RWF", "IQD", "MMK", "SYP", "CLP", "KRW", "AMD");
					    $os3 = array("ALL", "CVE", "AFN", "LKR", "ISK", "HUF", "NGN", "DZD", "KES", "KZT", "THB", "TWD", "JPY", "RUB", "HNL", "MZN", "NPR", "DJF", "INR", "PKR", "MKD", "RSD", "UYU", "BDT", "YER", "PHP", "JMD", "xxx", "xxx", "xxx", "xxx", "xxx", "xxx", "xxx");
					    $os4 = array("MOP", "BWP", "ARS", "ZAR", "ETB", "HKD", "NOK", "BOB", "ERN", "GTQ", "MXN", "SEK", "NIO", "PEN", "EGP", "MDL", "QAR", "UAH", "MUR", "SAR", "MAD", "GEL", "AED", "VEF", "HRK", "NAD", "DKK", "ZMK", "PLN", "CNY", "BRL", "TTD", "MYR", "CZK", "ILS", "LTL", "TRY");

					    if (in_array($this->base->code, $os1))
					    {
						    $decimal = 6;
					    }
					    if (in_array($this->base->code, $os2))
					    {
						    $decimal = 5;
					    }
					    if (in_array($this->base->code, $os3))
					    {
						    $decimal = 4;
					    }
					    if (in_array($this->base->code, $os4))
					    {
						    $decimal = 3;
					    }


					    if ($this->base->code == "BTC")
					    {
						    $decimal = 0;
					    } */

		if ($this->base->code == "GBP") {$currentRate1 = 1; }
			else {$currentRate1=$currentRate1; }

if (($hrate->rate/$currentRate1) * $amount>=1000) { $decimal=0; }
elseif ((($hrate->rate/$currentRate1) * $amount)<1000 && (($hrate->rate/$currentRate1) * $amount) >=100) {$decimal=1;}
elseif ((($hrate->rate/$currentRate1) * $amount)<100 && (($hrate->rate/$currentRate1) * $amount) >=10) {$decimal=2;}elseif ((($hrate->rate/$currentRate1) * $amount)<10 && (($hrate->rate/$currentRate1) * $amount) >=1) {$decimal=3;}elseif ((($hrate->rate/$currentRate1) * $amount)<1 && (($hrate->rate/$currentRate1) * $amount) >=0.1) {$decimal=4;}elseif ((($hrate->rate/$currentRate1) * $amount)<0.1 && (($hrate->rate/$currentRate1) * $amount) >=0.01) {$decimal=5;}
else {$decimal=6;}

					    $rowClass = ($count % 2 == 0) ? "even" : "odd";
					    if (empty($hrate->flag))
						    continue;


					    if (!in_array($hrate->continent, $continents) && $hrate->main != 1)
					    {
						    array_push($continents, $hrate->continent);
						    echo '<tr><td colspan="5"><h4>' . JText::_('MAIN_' . $hrate->continent) . '</h4></td></tr>';
					    }


					    ?>
                        <tr class="<?php echo $rowClass ?>">
                            <td class="country calc">


<img src="components/com_currencies/assets/images/blank.gif" class="flag flag-<?php echo strtolower(substr($hrate->flag, 0, 2)); ?>" alt="<?php echo $hrate->currency2?>" />

<a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $hrate->currency2 . '&layout=landing&Itemid=' . $this->params->get('pairs_itemid')) ?>"><?php echo  JText::_(JText::_('MAIN_' . strtoupper($hrate->currency_name))) . ' (' . $hrate->currency2 . ')' ?></a>
<?php 

?>

                            </td>
                            <td class="currencyex">

                                <span class="base-amount"><?php echo $all->format($amount * 1); ?></span>
                                <a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->base->code . '&layout=landing&Itemid=' . $this->params->get('pairs_itemid')) ?>"> <?php echo $this->base->code ?> </a>
                                =
                                <span class="currency-amount"><?php echo decima0((($hrate->rate/$currentRate1) * $amount), $decimal) . ' ' ?></span>
								
								
                                <a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $hrate->currency2 . '&layout=landing&Itemid=' . $this->params->get('pairs_itemid')) ?>"><?php echo $hrate->currency2 ?></a>

                            </td>
                            <td class="hidden-sm hidden-xs">
							    <?php setlocale(LC_ALL, sprintf($datelang, $glang, strtoupper($glang))); echo strftime('%e %b %Y %k:%M', strtotime($hrate->date)); ?>
                            </td>
                            <td class="pairingex">
                                <a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->base->code . '&to=' . $hrate->currency2 . '&value=500' ) ?>"> <?php echo JText::_('COM_CURRENCIES_CONVERT_T') . " " . $this->base->code . '/' . $hrate->currency2 ?></a>
                            </td>

                            <td class="hidden-sm hidden-xs">
                                <a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&layout=calculator&value=1000&base=' . $hrate->currency2) ?>"><?php echo JText::_('COM_CURRENCIES_CALCULATOR')." ".$hrate->currency2 ?></a>
                            </td>

                        </tr>
					    <?php $count++;
                    }
				} ?>
            </table>

        </div>
    </div>

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

        ?>
<?php echo sprintf (JText::_('CURRENCIES_3'),JText::_('MAIN_'.strtoupper($this->base->name)),$this->base->code,$con) ; ?>

    </div>
    <br>
    <div class="countries">

<?php echo sprintf (JText::_('CURRENCIES_4'),"<strong>","</strong>",JText::_('MAIN_'.strtoupper($this->base->name)),$this->base->code) ; ?>	

<textarea rows="6" cols="20" class="textareafoot">&lt;a href="https://<?php echo $_SERVER['SERVER_NAME'] . JRoute::_('index.php?option=com_currencies&view=pairs&layout=calculator&base='.$this->base->code) ?>"&gt;<?php echo JText::_('MAIN_'.strtoupper($this->base->name)) . " (" . $this->base->code . ")" . " " ?> <?php echo JText::_('COM_CURRENCIES_SMCALCULATOR') ?>&lt;/a&gt;</textarea>
    </div>


</div>



<script>
    jQuery(document).ready(function () {

        jQuery("#inputamount").focus(function () {
            jQuery("#inputamount").val('').toFixed(8);
        });
        jQuery('.validate').click(function (e) {
            var inputamount = jQuery("#inputamount").val().toFixed(8);
            if (inputamount == '') {
                e.preventDefault();
                jQuery("#inputamount").val("<?php echo JRequest::getVar('value'); ?>");
            }
            else return true;
        });
    });
</script>
<style>
    .show {
        display: table-row !important;
    }
</style>

