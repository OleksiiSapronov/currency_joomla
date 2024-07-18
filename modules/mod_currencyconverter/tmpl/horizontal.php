<?php 
$doc = JFactory::getDocument();
JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_currencies/models');
$ratesModel = JModelLegacy::getInstance('rates','CurrenciesModel');
$currencies = $ratesModel->getCurrencies();
$options = array();

foreach($currencies as $curr) {
    $options[] = JHTML::_('select.option', $curr->code, $curr->code);
}
$url = Jroute::_('index.php?option=com_currencies&task=rates.convert_ws');
$doc->addScriptDeclaration('
function convert(amount,base,curr2){
var data = {
"amount":amount,
"base":base,
"currency2":curr2
};
var base = jQuery();
    jQuery.ajax({
        type: "POST",
        url: "'.$url.'",
        data: data,
        success: function(data){
        
         jQuery("#converter_results").html(data);
        },
    });
}

function convertD(){
    var rate = 0;
    var base = jQuery("#from").val();
    var curr2 = jQuery("#to").val();
    var amount = jQuery("#amount").val();
    rate = convert(amount,base, curr2);
    jQuery("#converter_results").html(rate);
    return false;
}

function convertM(){
    var rate = 0;
    var base = jQuery("#from_m").val();
    var curr2 = jQuery("#to_m").val();
    var amount = jQuery("#amount_m").val();
    rate = convert(amount,base, curr2);
    jQuery("#converter_results_m").html(rate);
    return false;
}

jQuery("#CurrencySelectForm").keypress(function(event){

        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == "13"){
            var currency1 = document.getElementById("from").value;
            var currency2 = document.getElementById("to").value;
            var amount = document.getElementById("amount").value;
            convert(amount,currency1,currency2)
            event.preventDefault();
            return false;
        }
      
    });

');
?>
<div class="visible-xs visible-md" style="display: none">

    <table>
        <tr>
            <td> <input class="col-sm-12"  type="text" id="amount" placeholder="Amount"></td>
            <td id="converter_results" style="padding: 0 10px 10px; font-weight: bold"></td>
        </tr>
        <tr>
            <td>
                <?php echo JHTML::_('select.genericlist', $options, 'from', 'class="converter_inputs"', 'value', 'text', ''); ?>
               
            </td>
            <td>
                <?php echo JHTML::_('select.genericlist', $options, 'to', 'class="converter_inputs"', 'value', 'text', ''); ?>


            </td>
           
        </tr>
        <tr>
            <td></td>
            <td><input type="submit" class="btn btn-primary pull-right" value="convert" onclick="convertD()" /> </td>
        </tr>
    </table>

</div>


    <form class="form-inline">
<div class="form-group">
            <label class="sr-only" for="amount">Amount</label>
            <input type="amount" class="form-control" id="amount" placeholder="Amount">
        </div>
        <div class="form-group">

            <label class="sr-only" for="from">From</label>

    <?php echo JHTML::_('select.genericlist', $options, 'from', 'class="converter_inputs form-control" id="from"', 'value', 'text', ''); ?>

        </div>

        <div class="form-group">

            <label class="sr-only" for="to">To</label>

            <?php echo JHTML::_('select.genericlist', $options, 'to', 'class="converter_inputs form-control" id="to"', 'value', 'text', ''); ?>

        </div>

        

        <button type="submit" class="btn btn-primary" onclick="convertD()">Convert</button>

    </form>

