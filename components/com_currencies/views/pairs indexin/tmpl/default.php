<?php

 
$doc = JFactory::getDocument();
//$doc->addScript(JUri::root() . 'components/com_currencies/assets/js/holder.min.js');
$doc->addStyleSheet(JUri::root() . 'components/com_currencies/assets/css/morris.css');
$doc->addStyleSheet(JUri::root() . 'components/com_currencies/assets/css/flags.min.css');

$date                = JFactory::getDate();
$today               = $date->toISO8601();
$today               = date('M d, Y', strtotime($today));
$decimal             = $this->params->get('decimal_places');
$params              = JComponentHelper::getParams('com_currencies');
$dropdown_currencies = $params->get('dropdown_currencies');
$dropdown_currencies = explode(',', $dropdown_currencies);
$currenciesModel     = JModelLegacy::getInstance('currency', 'CurrenciesModel');
$top_currencies      = $currenciesModel->getCurrenciesByCodes($dropdown_currencies);
$lastUpdateDate      = $currenciesModel->getLastUpdateDate();
$ratesModel          = JModelLegacy::getInstance('rates', 'CurrenciesModel');
$gbase               = $this->params->get('base_currency');
$glang               = $this->params->get('component_lang');
$contents            = explode(' ', $this->currency2->name);
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
 


// echo "<br>"."<br>";
/// echo $lang->getTag(); echo "<br>".countDecimals($this->value); echo "<br>".$this->value ; 
 
 //echo "<br>". decima2($this->value);

setlocale(LC_ALL, sprintf($datelang, $glang, strtoupper($glang)));
if ($this->base->code == "GBP") {$funt = "Pound";}  else { $funt = end($contents);}

$contents1 = explode(' ', $this->base->name); if ($this->base->code == "GBP")
{ $funt1 = "Pound"; }
else { $funt1 = end($contents1); }

$decimal = 2;
if ($glang == "pl")
{
 $com1 = ",";
 $com2 = " ";
}
if ($glang == "en")
{
 $com1 = ".";
 $com2 = ",";
}

$decima = countDecimals($this->value);
if ($decima == 1) {$decimaly = 1; $lakdec   = 1;}
if ($decima > 1) { $decimaly = 2; $lakdec   = 2;}
if ($decima == 0){ $decimaly = 0; $lakdec   = 0;} 

$lakhdecima = countDecimals(($this->value) / 100000);
if ($lakhdecima == 1){ $lakdec = 1;}
if ($lakhdecima > 1){ $lakdec = 2;}
if ($lakhdecima == 0){ $lakdec = 0;}

//JText::_('MAIN_'.strtoupper($this->base->name))

if ($glang == "en") { $desc = "";}
else { $desc = " " . $this->base->name . ", ";}

if ($glang == "pl") { $msc = " " . JText::_('MAINMC_' . strtoupper($this->base->name));}
else { $msc = "";}

$pairtitle = sprintf(JText::_('CURRENCIES_12A'), $all->format($this->value), JText::_('MAIN_' . strtoupper($this->base->name)), $this->base->code, $this->currency2->code, number_format($this->current[1] * $this->value, 2, $com1, $com2), $msc, $funt,$this->currency2->code);

$pairkeywords = sprintf(JText::_('CURRENCIES_15'), $this->value, number_format($this->current[1] * $this->value, 0, $com1, $com2), JText::_('MAIN_' . strtoupper($this->base->name)), $desc, $this->currency2->code);

$pairdescription = sprintf(JText::_('CURRENCIES_10'), $this->base->code, JText::_('MAIN_' . strtoupper($this->base->name)), "", $all->format($this->value), number_format($this->current[1] * $this->value, 2, $com1, $com2), JText::_('MAIN_' . strtoupper($this->currency2->name)));

$document = JFactory::getDocument();
$document->setTitle($pairtitle);
$document->setMetaData("keywords", $pairkeywords);
$document->setMetaData("og:title", $pairtitle, 'property');
$document->setMetaData("description", $pairdescription, 'property');


?>

<div class="row default">
    <div>
        <div class="col-xs-12 col-md-9 col-lg-9">


            <div class="currenciesc">
                <div class="row top-section">
                    <div class="col-sm-12" style="margin-top:20px">
<h1> <?php

if (($this->base->code == "INR" || $this->base->code == "BDT" || $this->base->code == "LKR" ||
$this->base->code == "PKR" || $this->base->code == "MMK" || $this->base->code == "NPR") && $this->value >= 1000)
{$lakh = $this->value / 100000;}
else {$lakh = 0; } 

if (($this->base->code == "INR" || $this->base->code == "BDT" || $this->base->code == "LKR" ||
$this->base->code == "PKR" || $this->base->code == "MMK" || $this->base->code == "NPR") && $this->value >= 1000000)
{$crore = $this->value / 10000000;}
else {$crore = 0; }

if ($crore >= 100) {$croredec=decima0($crore); }
elseif ($crore >= 10 && $crore < 100) {$croredec=decima1($crore);}
elseif ($crore < 10 && $crore > 0.000001) {$croredec=decima2($crore);}
else {$croredec="";}

if ($lakh >= 100) {$displaylakh1=decima0($lakh); $displaylakh= ' (' . decima0($lakh) . ' ' . JText::_('MAIN_LAKH') . ') ';} 
elseif ($lakh >= 10 && $lakh < 100) {$displaylakh1=decima1($lakh); $displaylakh= ' (' . decima1($lakh) . ' ' . JText::_('MAIN_LAKH') . ') ';} 
elseif ($lakh < 10 && $lakh > 0.000001 )     {$displaylakh1=decima2($lakh); $displaylakh= ' (' . decima2($lakh) . ' ' . JText::_('MAIN_LAKH') . ') ';} 
else {$displaylakh="";}


if (($this->currency2->code == "INR" || $this->currency2->code == "BDT" || $this->currency2->code == "LKR" ||
$this->currency2->code == "PKR" || $this->currency2->code == "MMK" || $this->currency2->code == "NPR") && $this->value >= 1000)
{$lakh2 = ($this->current[1] * $this->value) / 100000;}
else {$lakh2 = 0; } 

if (($this->currency2->code == "INR" || $this->currency2->code == "BDT" || $this->currency2->code == "LKR" ||
$this->currency2->code == "PKR" || $this->currency2->code == "MMK" || $this->currency2->code == "NPR") && $this->value >= 1000000)
{$crore2 = ($this->current[1] * $this->value) / 10000000;}
else {$crore2 = 0; }

if ($crore2 >= 100) {$croredec2=decima0($crore2); }
elseif ($crore2 >= 10 && $crore2 < 100) {$croredec2=decima1($crore2);}
elseif ($crore2 < 10 && $crore2 > 0.000001) {$croredec2=decima2($crore2);}
else {$croredec2="";}

if ($lakh2 >= 100) {$displaylakh12=decima0($lakh2); } 
elseif ($lakh2 >= 10 && $lakh2 < 100) {$displaylakh12=decima1($lakh2);} 
elseif ($lakh2 < 10 && $lakh2 > 0.000001 )     {$displaylakh12=decima2($lakh2); } 
else {$displaylakh2="";}


echo sprintf (JText::_('CURRENCIES_7'),$displaylakh,JText::_('MAIN_'.strtoupper($this->currency2->name)),JText::_('MAINMC_'.strtoupper($this->base->name)),$all->format($this->value),$funt1,$funt );
  ?> </h1> </div>  </div>

  
                <div class="row  top-section">
                    <div class="col-sm-6  shadow">
      <?php
      $invsrvalue  = $this->inverse[1];
      $normalvalue = $this->current[1];
      $position    = 'advert1';
      $modules     = &JModuleHelper::getModules($position);
      foreach ($modules as $module)
      {
       echo JModuleHelper::renderModule($module);
      }

      if (($this->current[1] * $this->value) < 0.1)
       {$decimalx = 7; $valueh2right=decima7($this->current[1] * $this->value);}
                        elseif (($this->current[1] * $this->value) < 1)
       {$decimalx = 5; $valueh2right=decima5($this->current[1] * $this->value);}
                        elseif (($this->current[1] * $this->value) < 10)
       {$decimalx = 4; $valueh2right=decima4($this->current[1] * $this->value);}
                        elseif (($this->current[1] * $this->value) < 100)
       {$decimalx = 3; $valueh2right=decima3($this->current[1] * $this->value);}
                        elseif (($this->current[1] * $this->value) < 1000)
       {$decimalx = 2; $valueh2right=decima2($this->current[1] * $this->value);}
                        elseif (($this->current[1] * $this->value) < 10000)
       {$decimalx = 1; $valueh2right=decima1($this->current[1] * $this->value);}
      else
       {$decimalx = 0; $valueh2right=decima0($this->current[1] * $this->value);}

      if ($invsrvalue < 0.1)
       {$decimalz = 7; $invsrvalue1=decima7($this->inverse[1]);}
                        elseif ($invsrvalue < 1)
       {$decimalz = 5; $invsrvalue1=decima5($this->inverse[1]);}
                        elseif ($invsrvalue < 10)
       {$decimalz = 4; $invsrvalue1=decima4($this->inverse[1]);}
                        elseif ($invsrvalue < 100)
       {$decimalz = 3; $invsrvalue1=decima3($this->inverse[1]);}
                        elseif ($invsrvalue < 1000)
       {$decimalz = 2; $invsrvalue1=decima2($this->inverse[1]);}
                        elseif ($invsrvalue < 10000)
       {$decimalz = 1; $invsrvalue1=decima1($this->inverse[1]);}
      else
       {$decimalz = 0; $invsrvalue1=decima0($this->inverse[1]);}  

      if ($normalvalue < 0.1)
       {$decimalzz = 7; $normalvalue1=decima7($this->current[1]);}
                        elseif ($normalvalue < 1)
       {$decimalzz = 5; $normalvalue1=decima5($this->current[1]);}
                        elseif ($normalvalue < 10)
       {$decimalzz = 4; $normalvalue1=decima4($this->current[1]);}
                        elseif ($normalvalue < 100)
       {$decimalzz = 3; $normalvalue1=decima3($this->current[1]);}
                        elseif ($normalvalue < 1000)
       {$decimalzz = 2; $normalvalue1=decima2($this->current[1]);}
                        elseif ($normalvalue < 10000)
       {$decimalzz = 1; $normalvalue1=decima1($this->current[1]);}
      else
       {$decimalzz = 0; $normalvalue1=decima0($this->current[1]);} 

     ?>

                    </div>

                    <div class="col-sm-6 well center shandow">

         <h2 style="color:#F96010;">  <?php echo $all->format($this->value) . " ";
   echo $this->base->code . " = " . $valueh2right . ' ' . $this->currency2->code ?></h2>

      <?php if ($lakh > 0) { ?>
  <h2 style="color:#F96010;">  <?php echo $displaylakh1 . " " . JText::_('MAIN_LACS') . ' ' . $this->base->code. " = " .$valueh2right. ' ' . $this->currency2->code ?></h2>
      <?php } ?>
      
	  <?php if ($crore > 0) { ?>
  <h2 style="color:#F96010;">  <?php echo $croredec . " " . JText::_('MAIN_CRORE') . ' ' . $this->base->code. " = " .$valueh2right . ' ' . $this->currency2->code ?></h2>
      <?php } ?>

            <?php if ($lakh2 > 0) { ?>
  <h2 style="color:#F96010;">  <?php echo $all->format($this->value) . ' ' . $this->base->code. " = " .$displaylakh12. JText::_('MAIN_LACS') . ' ' . $this->currency2->code ?></h2>
      <?php } ?>
      
	  <?php if ($crore2 > 0) { ?>
  <h2 style="color:#F96010;">  <?php echo $all->format($this->value) . ' ' . $this->base->code. " = " .$croredec2 . JText::_('MAIN_CRORE') . ' ' . $this->currency2->code ?></h2>
      <?php } ?>

	  
	  

                        <table width="100%" cellspacing="5" cellpadding="5" align="center" class="praweokno">
                            <tbody>

                            <tr>
                                <td class="currencytd">
                                    <a href="<?php echo $this->base->page ?>">  <?php echo JText::_('COM_CURRENCIES_CHECK_THIS_CURRENCY') . "<br>" . JText::_('MAIN_' . strtoupper($this->base->name)) ?> </a>
                                    <p class="currencyone">
                                        1 <?php echo JText::_('MAIN_' . strtoupper($this->base->name)) ?>
                                        (<?php echo $this->base->symbol ?>)
                                        =<br> <?php echo $normalvalue1 ?> <?php echo $this->currency2->code ?>
                                        (<?php echo $this->currency2->symbol ?>)</p>
                                </td>

                                <td class="currencytd">
                                    <a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->currency2->code . '&layout=landing&Itemid=158') ?>"> <?php echo JText::_('COM_CURRENCIES_CHECK_THIS_CURRENCY') . "<br>" . JText::_('MAIN_' . strtoupper($this->currency2->name)) ?> </a>
                                    <p class="currencyone">
                                        1 <?php echo JText::_('MAIN_' . strtoupper($this->currency2->name)) ?>
                                        (<?php echo $this->currency2->symbol ?>)
                                        =<br> <?php echo $invsrvalue1?>
          <?php echo " " . JText::_('MAIN_' . strtoupper($this->base->name)) ?>
                                        (<?php echo $this->base->symbol ?>)</p>

                                </td>
                            </tr>


                            <!--<tr>


        <td class="currencyname" style="width: 150px;text-align: right;">
           
                <a href="<?php // echo JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->base->code . '&layout=landing&Itemid=158')  ?>"> <?php // printf(JText::_('COM_CURRENCIES_MORE_ABOUT_CURRENCY'),JText::_('MAIN_'.strtoupper($this->base->name)))  ?></a>    </td>
        <td></td>
        <td class="currencyname" style="width: 150px;text-align: left">
        <a href="<?php // echo  $this->currency2->article  ?>"> <?php //printf(JText::_('COM_CURRENCIES_MORE_ABOUT_CURRENCY'),JText::_('MAIN_'.strtoupper($this->currency2->name)))  ?></a>    </td>

</tr>-->
                            <tr>
                                <td colspan="3"><?php echo JText::_('COM_CURRENCIES_OUR_MONEY_CONVERTER') ?><?php echo " " . JText::_('COM_CURRENCIES_LAST_UPDATE') . strftime('%e %B %Y %k:%M:%S', strtotime($lastUpdateDate)); ?></td>
                            </tr>


                            </tbody>
                        </table>
                    </div>


                </div>


                <!-- converter -->

                <div class="row">
                    <div class="col-sm-12">

                        <form class="form-inline form-validate"
                              action="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&task=rates.convert&Itemid=' . $this->params->get('pairs_itemid')) ?>"
                              method="post">
                            <div class="form-group">

                                <label class="sr-only" for="inputamount"><?php echo JText::_('AMOUNT') ?></label>

                                <input type="number" step="0.000001" name="value"
                                       value="<?php if (JRequest::getVar('value'))
               {
                echo JRequest::getVar('value');
               } ?>" class="form-control required" id="inputamount" placeholder="<?php echo JText::_('AMOUNT') ?>"
                                       lang="en-150">

                            </div>
                            <div class="form-group">

                                <label class="sr-only" for="currencyselect_fromd">From</label>

                                <select class="required input-large" name="base" id="currencyselect_fromd">
                                    <optgroup label="<?php echo JText::_('COM_CURRENCIES_RECENT') ?>">
          <?php foreach ($top_currencies as $top_currency): ?>
                                            <option <?php echo $top_currency->code == $this->base->code ? 'selected="selected"' : '' ?>
                                                    value="<?php echo $top_currency->code ?>"
                                                    data-image="<?php echo Juri::root() . 'images/flags/' . $top_currency->flag ?>">
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
            <?php echo $cfrom->code ?>
                                                - <?php echo JText::_('MAIN_' . strtoupper($cfrom->name)) ?>
                                            </option>
          <?php } ?>
                                    </optgroup>
                                </select>

                            </div>

                            <div class="form-group">

                                <label class="sr-only" for="convertto">To</label>

                                <select name="to" class="required input-large" id="currencyselect_tod">
                                    <optgroup label="<?php echo JText::_('COM_CURRENCIES_RECENT') ?>">
          <?php foreach ($top_currencies as $top_currency): ?>
                                            <option <?php echo $top_currency->code == $this->currency2->code ? 'selected="selected"' : '' ?>
                                                    value="<?php echo $top_currency->code ?>"
                                                    data-image="<?php echo Juri::root() . 'images/flags/' . $top_currency->flag ?>">
            <?php echo $top_currency->code ?>
                                                - <?php echo JText::_('MAIN_' . strtoupper($top_currency->name)) ?>
                                            </option>
          <?php endforeach; ?>
                                    </optgroup>

                                    <optgroup label="<?php echo JText::_('COM_CURRENCIES_ALLCURR') ?>">
          <?php foreach ($this->allCurrencies as $cto) { ?>
                                            <option <?php echo $cto->code == $this->currency2->code ? 'selected="selected"' : '' ?>
                                                    value="<?php echo $cto->code ?>"
                                                    data-imagesrc="<?php echo Juri::root() . 'images/flags/' . $cto->flag ?>"
                                                    data-description="<?php echo JText::_('MAIN_' . strtoupper($cto->name)) ?>"><?php echo $cto->code ?>
                                                - <?php echo JText::_('MAIN_' . strtoupper($cto->name)) ?></option>
          <?php } ?>
                                    </optgroup>
                                </select>

                            </div>

                            <button type="submit"
                                    class="btn btn-primary validate"><?php echo JText::_('COM_CURRENCIES_CONVERT') ?></button>
                            <input type="hidden" value="com_currencies" name="option">
                            <input type="hidden" value="pairs" name="view">
                            <input type="hidden" value="rates.convert" name="task">


                        </form>


      <?php

if ($lakh) {$displaylakh=$displaylakh;}	  else {$displaylakh="";}
	  
$link3 = '<a href="' . JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->base->code . '&to=' . $this->currency2->code . '&value=' . $this->value . '&Itemid=' . $this->params->get('pairs_itemid')) . '">' . $all->format($this->value) . $displaylakh. " " . $this->base->code . " " . JText::_('MAIN_TO') . " " . $this->currency2->code . '</a>';


if ($datelang=="hi_IN.UTF-8" || $datelang=="en_CA.UTF-8"|| $datelang=="en_NZ.UTF-8") { 

$link4 = '<a href="' . JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->currency2->code . '&to=' . $this->base->code . '&value=' . $this->value . '&Itemid=' . $this->params->get('pairs_itemid')) . '">' .
JText::_('CURRENCIES_16A') . " " . $all->format($this->value) . " " . $this->currency2->code . " " . JText::_('MAIN_TO') . " " . $this->base->code . '</a>'; }

else {$link4 = '<a href="' . JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->currency2->code . '&to=' . $this->base->code . '&value=' . $this->value . '&Itemid=' . $this->params->get('pairs_itemid')) . '">' .
JText::_('CURRENCIES_16A') . " " . $all->format($this->value) . " " . $this->currency2->code . " " . JText::_('MAIN_TO') . " " . $this->base->code . '</a>';}

$linkcal = '<a href="' . JRoute::_('index.php?option=com_currencies&view=pairs&base='.$this->base->code.'&layout=calculator&Itemid=' . $this->params->get('pairs_itemid')) . '">' . JText::_('COM_CURRENCIES_SMCALCULATOR') . " " . JText::_('MAINMC_' . strtoupper($this->base->name)). " " . $this->base->code .'</a>';       
   

   
echo sprintf(JText::_('CURRENCIES_16'), number_format(($this->value), $decimaly, $com1, $com2), $this->base->code, $this->currency2->code, JText::_('MAIN_' . strtoupper($this->base->name)), $normalvalue1, $funt1, $this->currency2->name, JText::_('MAIN_' . strtoupper($this->currency2->name)), $link3, '<strong>', '</strong>', $link4, '<br>',JText::_('MAINMC_' . strtoupper($this->base->name)),$linkcal);


      ?>

                    </div>
                </div>
                <!-- end converter -->

    <?php
    $this->range  = array(1, 2, 5, 10, 50, 100, 500, 1000, 5000, 10000);
 if ($this->base->code=="BTC") {  $this->range2 = array(100, 200, 500, 1000, 5000, 10000, 50000, 100000, 500000, 1000000);}
else {  $this->range2 = array(1, 2, 5, 10, 50, 100, 500, 1000, 5000, 10000);}

    $os1 = array("VND", "IDR", "GNF", "KHR", "IRR", "PYG", "BYR", "COP", "UZS", "MGA", "UGX", "LAK", "xxx");
    if (in_array($this->base->code, $os1))
    {
     $this->range = array(10000, 50000, 100000, 500000, 1000000, 5000000, 10000000, 20000000, 50000000, 100000000);
    }
    $os2 = array("VND", "IDR", "GNF", "KHR", "IRR", "PYG", "BYR", "COP", "UZS", "MGA", "UGX", "LAK", "xxx");

    if (in_array($this->currency2->code, $os2))
    {
     $this->range2 = array(10000, 50000, 100000, 500000, 1000000, 5000000, 10000000, 20000000, 50000000, 100000000);
    }

    $os3 = array("", "XOF", "LBP", "KMF", "CRC", "LBP", "BIF", "TZS", "RWF", "IQD", "MMK", "SYP", "CLP", "KRW", "AMD");
    if (in_array($this->base->code, $os3))
    {
     $this->range = array(1000, 2000, 5000, 10000, 20000, 50000, 100000, 500000, 1000000, 5000000);
    }

    $os4 = array("", "XOF", "LBP", "KMF", "CRC", "LBP", "BIF", "TZS", "RWF", "IQD", "MMK", "SYP", "CLP", "KRW", "AMD");
    if (in_array($this->currency2->code, $os4))
    {
     $this->range2 = array(1000, 2000, 5000, 10000, 20000, 50000, 100000, 500000, 1000000, 5000000);
    }

    $os5 = array("ALL", "CVE", "AFN", "LKR", "ISK", "HUF", "NGN", "DZD", "KES", "KZT", "THB", "TWD", "JPY", "RUB", "HNL", "MZN", "NPR", "DJF", "INR", "PKR", "MKD", "RSD", "UYU", "BDT", "YER", "PHP", "JMD");
    if (in_array($this->base->code, $os5))
    {
     $this->range = array(100, 200, 500, 1000, 2000, 5000, 10000, 50000, 100000, 200000);
    }

    $os6 = array("ALL", "CVE", "AFN", "LKR", "ISK", "HUF", "NGN", "DZD", "KES", "KZT", "THB", "TWD", "JPY", "RUB", "HNL", "MZN", "NPR", "DJF", "INR", "PKR", "MKD", "RSD", "UYU", "BDT", "YER", "PHP", "JMD");
    if (in_array($this->currency2->code, $os6))
    {
     $this->range2 = array(100, 200, 500, 1000, 2000, 5000, 10000, 50000, 100000, 200000);
    }


    $os7 = array("MOP", "BWP", "ARS", "ZAR", "ETB", "HKD", "NOK", "BOB", "ERN", "GTQ", "MXN", "SEK", "NIO", "PEN", "EGP", "MDL", "QAR", "UAH", "MUR", "SAR", "MAD", "GEL", "AED", "VEF", "HRK", "NAD", "DKK", "ZMK", "PLN", "CNY", "BRL", "TTD", "MYR", "CZK", "ILS", "LTL", "TRY");
    if (in_array($this->base->code, $os7))
    {
     $this->range = array(10, 20, 50, 100, 200, 500, 1000, 5000, 10000, 20000);
    }

    $os8 = array("MOP", "BWP", "ARS", "ZAR", "ETB", "HKD", "NOK", "BOB", "ERN", "GTQ", "MXN", "SEK", "NIO", "PEN", "EGP", "MDL", "QAR", "UAH", "MUR", "SAR", "MAD", "GEL", "AED", "VEF", "HRK", "NAD", "DKK", "ZMK", "PLN", "CNY", "BRL", "TTD", "MYR", "CZK", "ILS", "LTL", "TRY");
    if (in_array($this->currency2->code, $os8))
    {
     $this->range2 = array(10, 20, 50, 100, 200, 500, 1000, 5000, 10000, 20000);
    }
    ?>

                <div class="row">

                    <div class="col-sm-12" style="margin-top:10px">
                        <h3>

<?php echo sprintf(JText::_('COM_CURRENCIES_CONVERSION_FORM'), $this->base->code, $this->currency2->code) ?></h3>

                        <div class="rowx">
                            <div class="colx">


        <?php ?>

                 <table class="table table-striped table-bordered">
                                    <th class="range2">  <?php echo $this->base->code . " " ?>
          <?php echo JText::_('COM_CURRENCIES_ON_THIS_TO') . " " . $this->currency2->code . " (" . JText::_('MAIN_' . strtoupper($this->currency2->name)) . ")" ?></th>
         <?php
         foreach ($this->range as $range)
         {
 if ($range * $this->current[1] >= 1000){$decimalf = decima0($range * $this->current[1]);}
          if ($range * $this->current[1] >= 100 && $range * $this->current[1] < 1000)
          {$decimalf = decima1($range * $this->current[1]);}
		  if ($range * $this->current[1] >= 10 && $range * $this->current[1] < 100)
          {$decimalf = decima2($range * $this->current[1]);}
	  	 if ($range * $this->current[1] >= 1 && $range * $this->current[1] < 10)
          {$decimalf = decima3($range * $this->current[1]);}
	  
    if ($range * $this->current[1] >= 0.1 && $range * $this->current[1] < 1)
          {$decimalf = decima4($range * $this->current[1]);}

     		 if ($range * $this->current[1] >= 0.001 && $range * $this->current[1] < 0.1)
          {$decimalf = decima5($range * $this->current[1]);}

          if ($range * $this->current[1] < 0.001)
          {$decimalf = decima7($range * $this->current[1]);}
     
          ?>
                                        <tr>

                                            <td class="range2">

                <a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->base->code . '&to=' . $this->currency2->code . '&value=' . ceil($range) . '&Itemid=' . $this->params->get('pairs_itemid')); ?>">

      <?php echo decima0($range) ." " .$funt1 . " (" . $this->base->code .") " ?>
                                                    =

              <a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->currency2->code . '&to=' . $this->base->code . '&value=' . ceil($range * $this->current[1]) . '&Itemid=' . $this->params->get('pairs_itemid')); ?>">

  <?php echo $decimalf . " " . $this->currency2->code ?>


                                                    </a></td>

                                        </tr>
         <?php } ?>
                                </table>

                            </div>
                            <div class="colx">

                                <table class="table table-striped table-bordered">


                                    <th class="range2"> <?php echo $this->currency2->code ?>
                                        to <?php echo $this->base->code . " (" . JText::_('MAIN_' . strtoupper($this->base->name)) . ")" ?></th>

         <?php
         foreach ($this->range2 as $range2)
         {

if ($range2 / (1 * $this->current[1]) >= 1000)
		  {$decimalf2 = decima0($range2 / (1 * $this->current[1]));$decimalff=0;}
if ($range2 / (1 * $this->current[1]) >= 100 && $range2 / (1 * $this->current[1]) < 1000)
          {$decimalf2 = decima2($range2 / (1 * $this->current[1]));$decimalff=2;}
if ($range2 / (1 * $this->current[1]) >= 10 && $range2 / (1 * $this->current[1]) < 100)
          {$decimalf2 = decima3($range2 / (1 * $this->current[1]));$decimalff=3;}
if ($range2 / (1 * $this->current[1]) >= 1 && $range2 / (1 * $this->current[1]) < 10)
          {$decimalf2 = decima4($range2 / (1 * $this->current[1]));$decimalff=4;}	 
if ($range2 / (1 * $this->current[1]) >= 0.1 && $range2 / (1 * $this->current[1]) < 1)
          {$decimalf2 = decima5($range2 / (1 * $this->current[1]));$decimalff=5;}	
if ($range2 / (1 * $this->current[1]) >= 0.001 && $range2 / (1 * $this->current[1]) < 0.1)
          {$decimalf2 = decima6($range2 / (1 * $this->current[1]));$decimalff=6;}
if ($range2 / (1 * $this->current[1]) < 0.001)
          {$decimalf2 = decima7($range2 / (1 * $this->current[1]));$decimalff=7;}	  
			 
//echo $decimalff;

          ?>
                                        <tr>

                                            <td class="range2">

     <a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->currency2->code . '&to=' . $this->base->code . '&value=' . ceil($range2) . '&Itemid=' . $this->params->get('pairs_itemid')); ?>">
<?php echo decima0($range2) ." " .$funt . " (" . $this->currency2->code .") " ?> 

 </a>
                                                =

     <a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->base->code . '&to=' . $this->currency2->code . '&value=' . ceil($range2 / (1 * $this->current[1])) . '&Itemid=' . $this->params->get('pairs_itemid')); ?>">
             <?php echo $decimalf2 ?><?php echo " ". $this->base->code ?>  </a>
			 
			 
			 


                                            </td>
                                        </tr>
         <?php } ?>
                                </table>

                            </div>
                        </div>


                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
      <?php if (count($this->rates) > 0) { ?>
                            <table class="table table-striped table-bordered">
                            <tr>
                                <th colspan="3">
                                    <h4><?php vprintf(JText::_('COM_CURRENCIES_HISTORY_DATE_RAGE'), array(strftime('%a %e %b, %Y', strtotime($this->rates[0]->date)), strftime('%a %e %b, %Y', strtotime($this->rates[count($this->rates) - 1]->date)))) ?></h4>
                                </th>
                            </tr>
                            <tr>
                                <th><?php echo JText::_('COM_CURRENCIES_DATE') ?></th>
                                <th> <?php echo $funt1 ?> = <?php echo $funt ?></th>
                            </tr>
       <?php $count = 0; ?>
       <?php foreach ($this->rates as $rate)
       {

 if (($rate->rate * $this->value) >= 1000)
	{$decimalf3 = decima0(($rate->rate * $this->value));}
if (($rate->rate * $this->value) >= 100 && ($rate->rate * $this->value) < 1000)
	{$decimalf3 = decima1(($rate->rate * $this->value));}
if (($rate->rate * $this->value) >= 10 && ($rate->rate * $this->value) < 100)
	{$decimalf3 = decima2(($rate->rate * $this->value));}
if (($rate->rate * $this->value) < 10)
	{$decimalf3 = decima5(($rate->rate * $this->value));}

        ?>
                                <tr>
                                    <td>
          <?php echo strftime('%A %e %B, %Y', strtotime($rate->date)); ?>
                                    </td>
                                    <td>


                                        <a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->base->code . '&to=' . $this->currency2->code . '&value=' . ceil($this->value) . '&Itemid=' . $this->params->get('pairs_itemid')); ?>">

           <?php echo $all->format($this->value)  ?><?php echo " ".  $this->base->code ?>

                                        </a> =


                                   

                        <a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->currency2->code . '&to=' . $this->base->code . '&value=' . ceil($rate->rate * $this->value) . '&Itemid=' . $this->params->get('pairs_itemid')); ?>">

           <?php echo $decimalf3 ?><?php echo " ". $this->currency2->code ?>


                                    </td>
                                </tr>
        <?php $count++;
       } ?>
                            <tr>
                                <!--<td><a href="<?php // echo JRoute::_('index.php?option=com_currencies&view=history&base='.$this->base->code.'&to='.$this->currency2->code.'&Itemid='.$this->params->get('pairs_itemid'))  ?>"><?php // echo JText::_('COM_CURRENCIES_GO_TO_CURRENCY_CALCULATOR')  ?></a> </td>-->
                                <td>
                                    <a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->base->code . '&layout=calculator&Itemid=' . $this->params->get('pairs_itemid')) ?>"><?php echo JText::_('COM_CURRENCIES_GO_TO_CURRENCY_CALCULATOR') ?></a>
                                </td>
                            </tr>
                            </table><?php } ?>

                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12"><?php if (isset($this->graph)) { ?>
                            <h4 class="center"><?php vprintf(JText::_('COM_CURRENCIES_EXCHANGE_GRAPH_HISTORY_TITLE'), array($this->historyDays)) ?> </h4>
                            <div id="graph"></div>
                            <div id="chart_div"></div>
                            <h5 class="center"><?php echo JText::sprintf('COM_CURRENCIES_EXCHANGE_RATES', JText::_('MAIN_' . strtoupper($this->base->name)), JText::_('MAIN_' . strtoupper($this->currency2->name))); ?></h5>
                            <div class="center">
                                <strong><?php echo JText::_('COM_CURRENCIES_MIN') ?></strong>=<?php if ((float) $this->graph->minValue > 0.1)
        {
         echo number_format((float) $this->graph->minValue, 3, $com1, $com2);
        }
        else
        {
         echo number_format((float) $this->graph->minValue, 6, $com1, $com2);
        } ?> [ <?php echo strftime('%e %b', strtotime($this->graph->dateMin)); ?> ]
                                <strong><?php //echo JText::_('COM_CURRENCIES_AVG') ?></strong><?php // echo number_format($this->graph->averange, 3) ?>
                                <strong><?php echo JText::_('COM_CURRENCIES_MAX') ?></strong>=<?php if ((float) $this->graph->maxValue > 0.1)
        {
         echo number_format((float) $this->graph->maxValue, 3, $com1, $com2);
        }
        else
        {
         echo number_format((float) $this->graph->maxValue, 6, $com1, $com2);
        } ?> [ <?php echo strftime('%e %b', strtotime($this->graph->dateMax)) ?> ]
                            </div>
      <?php } ?>
                    </div>
                </div>
                <div class="row other">

     <?php
     $otherCurrencies = $this->otherCurrencies;
     $count           = 0;
     ?>


                    <h4 class="h4dol"><?php echo number_format($this->value, countDecimals($this->value)) . ' ' . $this->base->code . ' ' . JText::_('COM_CURRENCIES_EXCHANGE_IN_OTHER_CURRENCY') ?></h4>
                    <div class="col-sm-6 col-xs-6 left">

                        <table class="table table-striped table-bordered">
       <?php foreach ($otherCurrencies as $currencykey => $currencyvalue)
       {
$currRate = $ratesModel->getCurrent($this->base->code, $currencyvalue->code);
if (($currRate[1] * $this->value) >= 1000)
	{$decimalf4 = decima0 (($currRate[1] * $this->value));}
if (($currRate[1] * $this->value) >= 100 && ($currRate[1] * $this->value) < 1000)
	{$decimalf4 = decima1(($currRate[1] * $this->value));}
if (($currRate[1] * $this->value) >= 10 && ($currRate[1] * $this->value) < 100)
	{$decimalf4 = decima2(($currRate[1] * $this->value));}
if (($currRate[1] * $this->value) >= 1 && ($currRate[1] * $this->value) < 10)
	{$decimalf4 = decima4(($currRate[1] * $this->value));}
if (($currRate[1] * $this->value) < 1)
	{$decimalf4 = decima6(($currRate[1] * $this->value));}		
		
		
		
        ?>
        <?php

        if ($count == 10)
         echo '</table></div><div class="col-sm-6 col-xs-6 right"><table class="table table-striped table-bordered">';
        
$contents1 = explode(' ', $currencyvalue->name); if ($currencyvalue->code == "GBP")
{ $funt2 = "Pound"; } else { $funt2 = end($contents1); }        
        
        ?>


                                <tr>
                                    <td>
                                        <a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $this->base->code . '&to=' . $currencyvalue->code . '&value=1&Itemid=' . $this->params->get('pairs_itemid')); ?>">
           <?php echo $all->format($this->value)   ." ". JText::_('MAIN_'.strtoupper($this->base->name)) ." (". $this->base->code .") "?>
                                        </a>
                                        =

                                        <a href="<?php echo JRoute::_('index.php?option=com_currencies&view=pairs&base=' . $currencyvalue->code . '&to=' . $this->base->code . '&value=' . ceil($currRate[1] * $this->value) . '&Itemid=' . $this->params->get('pairs_itemid')); ?>">
           <?php echo $decimalf4 
           ?><?php echo " ".$funt2 . " (" .$currencyvalue->code .") "  ?>
                                        </a>
                                    </td>
                                </tr>
        <?php $count++;
       } ?>
                        </table>
                    </div>
                </div>
            </div>


        </div>
        <div class="col-xs-12 col-md-3 col-lg-3 side-modules">

            <br>{module module="Currency List" showtitle="false"}
   <?php //  {module module="Last queries" showtitle="true" title="<?php // echo JText::_('LASTQ').' "}'?>
   <?php // echo '{module module="Last 24 hour" showtitle="true"}'; ?>

        </div>
    </div>
</div>


<script>
    jQuery(document).ready(function () {
        //    jQuery("#inputamount").keyup(function(){
        //        var amount = jQuery(this).val().replace(/[^\d.,]/g, '');
        ////       amount = amount.replace(/^[0]/g, '');
        //  jQuery(this).val(amount).toFixed(8);
        //    });

        jQuery("#inputamount").focus(function () {
            jQuery("#inputamount").val('');
        });
        //
        // jQuery("#inputamount").blur(function() {
        //  var amount = jQuery("#inputamount").val();
        //  if(amount=='')
        //  {
        //   jQuery("#inputamount").val("<?php echo JRequest::getVar('value'); ?>");
        //  }
        // });

        jQuery('.validate').click(function (e) {
            var inputamount = jQuery("#inputamount").val();
            if (inputamount == '') {
                e.preventDefault();
                jQuery("#inputamount").val("<?php echo JRequest::getVar('value'); ?>");
            } else
                return true;
        });
    });
</script>
