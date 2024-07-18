<style>
.control-group {
    display:table;
}

.control-group label,
.control-group select {
    display: table-cell;
}

.control-group label {
    width:1%;
    white-space:nowrap;
    padding-right:10px;
}

.control-group select {
    width:99%;
}
</style>
<?php 
$doc = JFactory::getDocument();
JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_currencies/models');
$ratesModel = JModelLegacy::getInstance('rates','CurrenciesModel');
$currencies = $ratesModel->getCurrencies();
$options = array();

foreach($currencies as $curr) {
    $options[] = JHTML::_('select.option', $curr->code, $curr->code.' - '.$curr->name);
}
$url = JRoute::_('index.php?option=com_currencies&task=rates.convert_ws');
$doc->addScriptDeclaration('
jQuery(document).ready(function(){
	// process the form
    jQuery("#converter-form").submit(function(event) {
         var amount =parseFloat(jQuery("#amount").val());
		 if(isNaN(amount)){
			    jQuery("#converter_results").addClass("alert");
				if(jQuery("#converter_results").hasClass("alert-info")){
					
				 jQuery("#converter_results").removeClass("alert-info");
				}
				 jQuery("#converter_results").addClass("alert-warning");
				 jQuery("#converter_results").html("Please enter a positive number");
				 return false;
				 event.preventDefault();
		 }
        // get the form data
        var formData = {
            "base"              : jQuery("#from").val(),
            "currency2"             : jQuery("#to").val(),
            "amount"    : amount
        };
        // process the form
        jQuery.ajax({
            type        : "POST", // define the type of HTTP verb we want to use (POST for our form)
            url         : "'.$url.'", // the url where we want to POST
            data        : formData, // our data object
        })
            // using the done promise callback
            .done(function(data) {
				 if(jQuery("#converter_results").hasClass("alert-warning")){
					 
				 jQuery("#converter_results").removeClass("alert-warning");
				 }
				 jQuery("#converter_results").addClass("alert");
				 jQuery("#converter_results").addClass("alert-info");
				 jQuery("#converter_results").html(formData.amount+" "+formData.base+" = "+data+" "+formData.currency2);

                // here we will handle errors and validation messages
            });

        // stop the form from submitting the normal way and refreshing the page
        event.preventDefault();
    });

});

jQuery("#converter-form").keypress(function(event){

        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == "13"){
            var currency1 = document.getElementById("from").value;
            var currency2 = document.getElementById("to").value;
            var amount = document.getElementById("amount").value;
           jQuery("#converter-form").submit();
        }
      
    });

');
?>
<div class="hidden-sm calculator">
<div id="converter_results"></div>
 <form  id="converter-form">

        <div class="form-group">

            <label for="amount">Amount</label>

            <input type="amount" class="form-control" id="amount" placeholder="Amount">

        </div>

        <div class="form-group">

            <label for="from">From</label>
 <?php echo JHTML::_('select.genericlist', $options, 'from', 'class="form-control"', 'value', 'text', ''); ?>
        </div>
<div class="form-group">
	<label for="to">To</label>
	 <?php echo JHTML::_('select.genericlist', $options, 'to', 'class="form-control"', 'value', 'text', ''); ?>
</div>
       

        <button type="submit"  class="btn btn-primary">Convert</button>

    </form>
    </div>
	
<script>
jQuery(document).ready(function(){
    jQuery("#amount").keyup(function(){
        var amount = jQuery(this).val().replace(/[^\d.]/g, '');
		jQuery(this).val(amount);        
    });	
});
</script> 