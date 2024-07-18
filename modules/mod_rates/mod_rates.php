  <?php

//function chartcmp($a,$b) {
//	if ($a['rid'] < $b['rid']) {
//		return -1;
//	} else {
//		return 1;
//	}
//}
$showZeroValues =$params->get("show_zero_values");
if ((isset($_REQUEST['format']))&&($_REQUEST['format']=='raw')) {
	define('_JEXEC', 1);
	define('DS', DIRECTORY_SEPARATOR);
	define('JPATH_BASE', dirname('../../index.php'));
	include('../../configuration.php');
	include('../../includes/defines.php');
	include('../../includes/framework.php');
	
	$JConfig = new JConfig;
	
	function getExchangeRate($from,$to) {
		if ($from==$to) {return 1;}
		$JConfig = new JConfig;
		$array = mysql_fetch_assoc(mysql_query("SELECT value FROM {$JConfig->dbprefix}rates_history WHERE currency_from = '$from' AND currency_to = '$to' ORDER BY `timestamp` DESC LIMIT 1"));
		return floatval($array['value']*100)/100;
	}
	
	mysql_connect($JConfig->host, $JConfig->user, $JConfig->password);
	mysql_select_db($JConfig->db);
	$currency1 = substr(mysql_real_escape_string($_REQUEST['currency1']),0,3);
	$currency2 = substr(mysql_real_escape_string($_REQUEST['currency2']),0,3);
	
	$rate12 = getExchangeRate($currency1,$currency2);
	$rate21 = getExchangeRate($currency2,$currency1);
	
	echo "var rate12 = $rate12; var rate21 = $rate21;";
	
	die();
	
} else {
	?>
	
	<script>
	var getElementsByClassName = function (className, tag, elm){
		if (document.getElementsByClassName) {
			getElementsByClassName = function (className, tag, elm) {
				elm = elm || document;
				var elements = elm.getElementsByClassName(className),
					nodeName = (tag)? new RegExp("\\b" + tag + "\\b", "i") : null,
					returnElements = [],
					current;
				for(var i=0, il=elements.length; i<il; i+=1){
					current = elements[i];
					if(!nodeName || nodeName.test(current.nodeName)) {
						returnElements.push(current);
					}
				}
				return returnElements;
			};
		}
		else if (document.evaluate) {
			getElementsByClassName = function (className, tag, elm) {
				tag = tag || "*";
				elm = elm || document;
				var classes = className.split(" "),
					classesToCheck = "",
					xhtmlNamespace = "http://www.w3.org/1999/xhtml",
					namespaceResolver = (document.documentElement.namespaceURI === xhtmlNamespace)? xhtmlNamespace : null,
					returnElements = [],
					elements,
					node;
				for(var j=0, jl=classes.length; j<jl; j+=1){
					classesToCheck += "[contains(concat(' ', @class, ' '), ' " + classes[j] + " ')]";
				}
				try	{
					elements = document.evaluate(".//" + tag + classesToCheck, elm, namespaceResolver, 0, null);
				}
				catch (e) {
					elements = document.evaluate(".//" + tag + classesToCheck, elm, null, 0, null);
				}
				while ((node = elements.iterateNext())) {
					returnElements.push(node);
				}
				return returnElements;
			};
		}
		else {
			getElementsByClassName = function (className, tag, elm) {
				tag = tag || "*";
				elm = elm || document;
				var classes = className.split(" "),
					classesToCheck = [],
					elements = (tag === "*" && elm.all)? elm.all : elm.getElementsByTagName(tag),
					current,
					returnElements = [],
					match;
				for(var k=0, kl=classes.length; k<kl; k+=1){
					classesToCheck.push(new RegExp("(^|\\s)" + classes[k] + "(\\s|$)"));
				}
				for(var l=0, ll=elements.length; l<ll; l+=1){
					current = elements[l];
					match = false;
					for(var m=0, ml=classesToCheck.length; m<ml; m+=1){
						match = classesToCheck[m].test(current.className);
						if (!match) {
							break;
						}
					}
					if (match) {
						returnElements.push(current);
					}
				}
				return returnElements;
			};
		}
		return getElementsByClassName(className, tag, elm);
	};
	//array of titles of all currencies
	var arrCurrencyTitles = {
		<?php
			$db = JFactory::getDbo();
			$db->setQuery("SELECT params FROM #__modules WHERE module = 'mod_rates' AND published = '1' ");
			$db->query();
			$result = $db->loadAssocList();
			$objParams = json_decode($result[0]["params"]);
			$arrAllCurrencies = array_unique(array_merge(explode(",",$objParams->currencies_from),explode(",",$objParams->currencies_to)));
			foreach ($arrAllCurrencies as $elem) {
				echo "'$elem':'" . JText::_("CURRENCY_$elem") . "','{$elem}1':'" . JText::_("CURRENCY_{$elem}1") . "',";
			} 
		?>
	} 

	function changeCurrency(currency1,currency2) {	
		//we need to recieve rates via ajax
	    var xmlHttpReq = false;
	    var self = this;
	    if (window.XMLHttpRequest) {
	        self.xmlHttpReq = new XMLHttpRequest();
	    } else if (window.ActiveXObject) {
	        self.xmlHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
	    }
	    self.xmlHttpReq.open('POST', '/modules/mod_rates/mod_rates.php?format=raw', true);
	    self.xmlHttpReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	    self.xmlHttpReq.onreadystatechange = function() {
	        if (self.xmlHttpReq.readyState == 4) {
	            eval(self.xmlHttpReq.responseText);
				for (var i=0; i < document.getElementsByClassName('currency1').length; i++) {
				    document.getElementsByClassName('currency1')[i].innerHTML = currency1;
				}
				for (var i=0; i < document.getElementsByClassName('currency2').length; i++) {
				    document.getElementsByClassName('currency2')[i].innerHTML = currency2;
				}
				for (var i=0; i < document.getElementsByClassName('table-rates').length; i++) {
					if (document.getElementsByClassName('table-rates')[i].getAttribute('direction')=='12') {
				    	document.getElementsByClassName('table-rates')[i].innerHTML = (document.getElementsByClassName('table-rates')[i].getAttribute('amount') * rate12).toFixed(2);
					} else {
						document.getElementsByClassName('table-rates')[i].innerHTML = (document.getElementsByClassName('table-rates')[i].getAttribute('amount') * rate21).toFixed(2);
					}
				}
				for (var i=0; i < document.getElementsByClassName('amount').length; i++) {
					document.getElementsByClassName('amount')[i].innerHTML = parseFloat(document.getElementById('amount').value.replace(",", "."));
;
				}
				
				for (var i=0; i < document.getElementsByClassName('result').length; i++) {
					document.getElementsByClassName('result')[i].innerHTML = (parseFloat(document.getElementById('amount').value.replace(",", ".")) * rate12).toFixed(2);
				}

				for (var i=0; i < document.getElementsByClassName('currency1full').length; i++) {
					document.getElementsByClassName('currency1full')[i].innerHTML = arrCurrencyTitles[currency1];
				}

				for (var i=0; i < document.getElementsByClassName('currency2full').length; i++) {
					document.getElementsByClassName('currency2full')[i].innerHTML = arrCurrencyTitles[currency2];
				}

				for (var i=0; i < document.getElementsByClassName('currency2full1').length; i++) {
					document.getElementsByClassName('currency2full1')[i].innerHTML = arrCurrencyTitles[currency2+"1"];
				}
							
	        }
	    }
	    self.xmlHttpReq.send('currency1='+currency1+'&currency2='+currency2);
	}
	
function printIt() {
	var win = window.open('', 'Print version', 'resizable=false');
	if (win) {
		var target = document.getElementById('table-rates');
		var wrap = document.createElement('div');
		wrap.appendChild(target.cloneNode(true));
		win.document.writeln(wrap.innerHTML);
		win.document.close();
		win.focus();
		win.print();
	}
	return false;
}
	
	</script>
	<?php 
	require JModuleHelper::getLayoutPath('mod_rates');
}





