<?php

// Customized Cron File to Fetch and Store Data for Exchange Rates from Yahoo Servicers.
//require('connect.php');

$base = 'VND'; // Set Base/Default Currency for getting exchange rates against.

/* ------------------------------------------------------------------------------- */
function getExchangeRate($from,$to) {
	$url = 'http://finance.yahoo.com/d/quotes.csv?e=.csv&f=sl1d1t1&s='. $from . $to .'=X';
	$handle = @fopen($url, 'r');
	
	if ($handle) {
		$result = fgets($handle, 4096);
		fclose($handle);
	}
	$allData = explode(',',$result);
	$c1 = $allData[1];
	return $c1;
}

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

if (file_exists('../../defines.php')) {
	include_once '../../defines.php';
}

if (!defined('_JDEFINES')) {
	define('JPATH_BASE', '../..');
	require_once JPATH_BASE.'/includes/defines.php';
}

require_once JPATH_BASE.'/includes/framework.php';

$db = JFactory::getDbo();


/* ------------------------------------------------------------------------------- */
// Lets build a list of all currencies from our text file.

$curry = "";
$handle = @fopen("currencies.txt", "r");
if ($handle) {
    while (($buffer = fgets($handle, 8096)) !== false) {
        $curries[] = trim($buffer);
    }
    if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($handle);
}

$curlist = "";
foreach($curries as $curry){
	$curlist .= $curry."$base=X+";
}

/* ------------------------------------------------------------------------------- */

// Lets Call Yahoo Feed for data for our Currencies list.
$feedurl = 'http://download.finance.yahoo.com/d/quotes.csv?f=sl1d1t1&s='.$curlist;
$handle = @fopen("$feedurl", "r");
if ($handle) {
    while (($burfi = fgets($handle, 8096)) !== false) {
        $entries[] = trim($burfi);
    }
    if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
    }
    fclose($handle);
}

// Lets get them entered into database
$entry = "";
foreach($entries as $entry){

	$entry = str_replace('"', '', $entry);
	$tab = explode(',', $entry);

	$currency_from = trim(str_replace("$base=X", "", $tab[0]));
	$currency_to = trim($base);
	$value = trim($tab[1]);
	$timestamp = trim("$tab[2] $tab[3]");
	$now = date("Y-m-d H:i:s");

	$curr[$currency_from]=$value;

// Entering Data into Database
//mysql_query("INSERT IGNORE INTO #__rates_history (timestamp, currency_from, currency_to, value) VALUES('$now', '$currency_from', '$currency_to', '$value') ") or die(mysql_error('Could not Enter Data into Database.'));

	$query = $db->getQuery(true);
			$query->insert('#__rates_history');
			$query->set("`timestamp` = '$now', `currency_from` = '$currency_from', `currency_to` = '$currency_to', `value`=" . $value);
			$db->setQuery($query);
			$db->query();		
			$query->set("`timestamp` = '$now', `currency_from` = '$currency_from', `currency_to` = '$currency_to', `value`=" . $value);
			$db->setQuery($query);
			$db->query();	


}


//print_r ($curr);

//interconversion  loop begins
$i=0;
foreach ($curr as $key => $arr)
{

	foreach($curr as $key1 => $arr1)
		{
		if($key1==$key)
			{
							
			}
		else
		{
//		echo "s.no.".$i."//".$key."=>".$key1."==>>";
		$convert=$arr/$arr1;
//		echo $convert."<br />";
	
//mysql_query("INSERT IGNORE INTO #__rates_history (timestamp, currency_from, currency_to, value) VALUES('$now', '$key', '$key1', '$convert') ") or die(mysql_error('Could not Enter Data into Database.'));	


	$query = $db->getQuery(true);
			$query->insert('#__rates_history');
			$query->set("`timestamp` = '$now', `currency_from` = '$key', `currency_to` = '$key1', `value`=" . $convert);
			$db->setQuery($query);
			$db->query();		
			$query->set("`timestamp` = '$now', `currency_from` = '$key', `currency_to` = '$key1', `value`=" . $convert);
			$db->setQuery($query);
			$db->query();	
      
      

		$i++;
		}

		
		}}
//interconversion loop ends

//mysql_close($link); // Terminating Database Connection - Rest in Peace.

?>
