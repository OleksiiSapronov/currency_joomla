<?php 
$url= $_SERVER['SERVER_NAME'];
if (substr($url, -1) == '/')
   $url = substr($url, 0, -1);
echo $url;   
define('SITEURL', $url); // Base URL of your site
define('SECRETKEY', 'c8h_4_y9r1nPGC--udpBocyOLb8GK6Ne'); // Your secret key
define('PROFILE',1); // The profile's ID

// ====================== DO NOT MODIFY BELOW THIS LINE ======================
$curl_handle=curl_init();
curl_setopt($curl_handle,CURLOPT_URL,
SITEURL.'/index.php?option=com_akeeba&view=backup&key='.
SECRETKEY.'&profile='.PROFILE);
curl_setopt($curl_handle,CURLOPT_FOLLOWLOCATION,TRUE);
curl_setopt($curl_handle, CURLOPT_USERAGENT, "backup");
curl_setopt($curl_handle,CURLOPT_MAXREDIRS,10000); # Fix by Nicholas
curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
$buffer = curl_exec($curl_handle);
curl_close($curl_handle);
if (empty($buffer))
    echo "Sorry, the backup didn't work.";
else
    echo $buffer;   
    
exit;    
?>