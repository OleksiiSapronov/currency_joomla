<style>
    #rates-module {
        font-family: arial,"Lucida Sans Unicode","Lucida Grande",Sans-Serif;
        border: 1px solid #CDCDCD;
        height:250px;
        font-size: 12px;
    //  color: #535353;
    padding-left: 10px;

        }

    #rates-module1 {
        margin-bottom:25px;
        text-align:center;
    }

    .lastupdatedate {
        color: #9FA1A0;
        font-family:arial;
        font-size: 11px;
        top:-11px;
    }


    #amount {
        border: 1px solid #CDCDCD;
        font-size: 16px;
        height: 20px
        line-height: 26pt;
        margin-bottom: 10px;
        width: 170px;
        z-index: 1;
    }

    .h13 {

        font-size: 22px;
        color: #FFEB9E;
        text-shadow: 0 0 6px rgba(255, 144, 0, 0.5);
    }

    #ad {
        width:320px;
        float:right;
    }

    #rates-module #ad {
        border-left: 1px solid gray;
        padding-left: 10px;
        margin-left: 10px;
        display: inline-block;
        vertical-align: top;
    }
    #convert-table td { border: 0 none; }
    #convert-table {border: 0px none;}
    .print-header, .graph-title {text-align: center; font-weight: bold;}
    .result-text {font-size: 14px;   	font-weight: bold;border-top: 1px solid gray; padding-top: 5px; margin-top: 5px;  }
    #scurrency1, #scurrency2, {width: 200px; line-height: 1.3; margin-bottom: 10px;}
    #table-rates {
        margin-top: 5px;
        background: none repeat scroll 0 0 #FFFFFF;
        border-collapse: collapse;
        font-family: arial,"Lucida Sans Unicode","Lucida Grande",Sans-Serif;
        font-size: 12px;
        text-align: left;
    }
    #table-rates th {
        border-bottom: 2px solid #6678B1;
        color: #4C4546;
        font-size: 14px;
        font-weight: normal;
        padding: 10px 8px;
    }
    #table-rates td {
        border-bottom: 1px solid #CCCCCC;
        color: #4C4546;
        padding: 6px 8px;
    }
    #table-rates tbody tr:hover td {
        color: #000099;
    }


</style>
<script>


    <?php 
    
    //recieve default currency 
    $strCurrency1 = $params->get("cid1");
    $strCurrency2 = $params->get("cid2");
    //recieve titles for it
    $strCurrency1FullTitle = JText::_("CURRENCY_" . strtoupper($strCurrency1));
    $strCurrency1FullTitle1 = JText::_("CURRENCY_" . strtoupper($strCurrency1)."1");
  $strCurrency2FullTitle = JText::_("CURRENCY_" . strtoupper($strCurrency2));
    $strCurrency2FullTitle1 = JText::_("CURRENCY_" . strtoupper($strCurrency2)."1");
  
    //get default amount
    $intDefaultAmount = (int)$params->get("amount");
    //we need to know rate between currencies
    $db = JFactory::getDbo();
    //rate #1
    $db->setQuery("SELECT * FROM #__rates_history WHERE currency_from = '$strCurrency1' AND `currency_to` = '$strCurrency2' ORDER BY `timestamp` DESC LIMIT 1");
    $db->query();
    $result = $db->loadAssoc(); 
    $rate1 = floatval($result['value']*100)/100;
    //rate #2
    echo "var rate12 = {$rate1}; ";
    $db->setQuery("SELECT * FROM #__rates_history WHERE currency_from = '$strCurrency2' AND `currency_to` = '$strCurrency1' ORDER BY `timestamp` DESC LIMIT 1");
    $db->query();
    $result = $db->loadAssoc();
    $rate2 = floatval($result['value']*100)/100;
    echo "var rate21 = {$rate2}; ";
    //lastupdate
    $db->setQuery("SELECT `timestamp` FROM #__rates_history WHERE 1 ORDER BY `timestamp` DESC LIMIT 1");
    $db->query();
    $result = $db->loadAssoc();
    $strLastUpdateDate = date("d.m.Y H:i",strtotime($result['timestamp']));
    echo "var currency1 = '$strCurrency1'; ";
    echo "var currency2 = '$strCurrency2'; ";
    echo "var currency1title = '$strCurrency1FullTitle'; ";
    echo "var currency2title = '$strCurrency2FullTitle'; ";
 
    ?>

    jQuery(document).ready(function(){
        document.getElementById("CurrencySelectForm").reset();
        jQuery('#CurrencySelectForm').keypress(function(event){

            var keycode = (event.keyCode ? event.keyCode : event.which);
            if(keycode == '13'){
                var currency1 = document.getElementById('scurrency1').value;
                var currency2 = document.getElementById('scurrency2').value;
                changeCurrency(currency1,currency2)
                event.preventDefault();
                return false;
            }

        });
    })


</script>

<div id="rates-module1">

    <span class="h11"> <h3><?php echo JText::_('EXCHANGE_RATE'); ?> </h3></span>

    <span class="h13">	1 <span class="currency1full"><?php echo $strCurrency1FullTitle; ?></span> (<span class="currency1"><?php echo $strCurrency1; ?></span>) = <span class='table-rates' direction='12' amount='1'><?php echo number_format($rate1, 2, '.', ''); ?></span> <span class="currency2full1"><?php echo $strCurrency2FullTitle1; ?></span> (<span class="currency2"><?php echo $strCurrency2; ?></span>)</span> <div> <span class="lastupdatedate"><?php echo JText::_('ACTUAL_EXCHANGE_RATE_FROM') ?>: <? echo $strLastUpdateDate; ?></span>  </div>





</div>



<div>
    <div style="float:left;padding-bottom:10px;margin-right:10px">


        <div id="rates-module">

            <div style="vertical-align: top;">
                <div style="display: inline-block; vertical-align: top; width:290px;">
                    <form id="CurrencySelectForm" style="vertical-align: top; margin-bottom:5px;">
                        <?php echo JText::_('AMOUNT'); ?><br><input id="amount" value="<?php echo $intDefaultAmount; ?>">
                        <div>
                            <?php echo JText::_('FROM'); ?>:<br>
                            <select id="scurrency1" onchange="currency1 = this.value; ">
                                <?php
                                $arrCurrencies = explode(",", $params->get("currencies_from"));
                                if (is_array($arrCurrencies)) {
                                    foreach ($arrCurrencies as $strCurrency) {
                                        echo "<option".(($strCurrency==$strCurrency1) ? " selected " : "") ." value='$strCurrency'>" . JText::_("CURRENCY_$strCurrency") . " ($strCurrency)</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div>
                            <?php echo JText::_('TO'); ?>:<br>
                            <select id="scurrency2" onchange="currency2 = this.value; ">
                                <?php
                                $arrCurrencies = explode(",", $params->get("currencies_to"));
                                if (is_array($arrCurrencies)) {
                                    foreach ($arrCurrencies as $strCurrency) {
                                        echo "<option".(($strCurrency==$strCurrency2) ? " selected " : "")." value='$strCurrency'>" . JText::_("CURRENCY_$strCurrency") . " ($strCurrency)</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>


                        <span style="background-color: #5B74A8; display: inline-block; padding: 4px; margin-top: 6px; font-family: 'arial', 'Lucida Sans Unicode','Lucida Grande',Sans-Serif; font-weight: bold; color: white; border-width: 1px; border-style:solid; cursor: pointer; border-color: #29447E #29447E #1A356E;" onclick="currency1 = document.getElementById('scurrency1').value; currency2 = document.getElementById('scurrency2').value; changeCurrency(currency1,currency2);return false;"><?php echo JText::_('CONVERT'); ?></span>

                    </form>
                    <div class="result-text"><?php echo JText::_('Result'); ?>: <span class="amount"><?php echo $intDefaultAmount; ?></span> <span class="currency1"><?php echo $strCurrency1; ?></span> = <span class="result"><? echo number_format($intDefaultAmount*$rate1, 2, '.', ''); ?></span> <span class="currency2"><?php echo $strCurrency2;  ?></span>





                    </div>
                    <span class="lastupdatedate"><?php echo JText::_('ACTUAL_EXCHANGE_RATE_FROM') ?>: <? echo $strLastUpdateDate; ?></span>
                </div></div>

        </div>
    </div>
    <div style="float:left;">
        <div id="ad">
            <? echo $params->get('advertice'); ?>
        </div>
    </div>

</div>
