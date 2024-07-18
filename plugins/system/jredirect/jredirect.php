<?php
/**
 * JRedirect plugin
 * 
 * @author Ross Farinella
 * @version 1.0.0
 * @license GPL
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

global $mainframe;
$mainframe->registerEvent('onAfterRoute', 'plgSystemCheckJRedirect');

/**
 * Checks to see if the current URL being requested is one of the "special" URLs 
 * 	and redirects the application as necessary
 */
function plgSystemCheckJRedirect() 
{
	global $mainframe;
	
	// get the plugin parameters
	$tmp = JPluginHelper::getPlugin("system","jredirect");
	$params = new JParameter($tmp->params);
	
	// get the current URI
	$current = JRequest::getURI(); // "/something.html"
	
	$urls = $params->get('urls');
	$urls = explode("\n",$urls);
	foreach($urls as $url) 
	{
		// get the user-entered urls
		list($toCheck,$toRedirect) = explode("|",$url);
		
		// check if we're at this url
		if($current == "/".$toCheck) {
			// do the redirect
//			$mainframe->redirect($toRedirect);
	
 JFactory::getApplication()->redirect( $toRedirect, $toCheck, $msg_type, true );

  
  	}
	}
}


?>
