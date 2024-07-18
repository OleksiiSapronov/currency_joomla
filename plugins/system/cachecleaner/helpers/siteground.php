<?php
/**
 * Plugin Helper File: SiteGround
 *
 * @package         Cache Cleaner
 * @version         3.7.0
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2015 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

/**
 * Based on:
 * Siteground Joomla Cache Plugin (jSGCache)
 *
 * @author       George Penkov
 * @category     Siteground Joomla Plugins
 * @package      Siteground Joomla Cache Plugin
 */

require_once __DIR__ . '/cache.php';

class plgSystemCacheCleanerHelperSiteGround extends plgSystemCacheCleanerHelperCache
{
	public function purge()
	{
  	$purgeRequest = str_replace(array('administrator/index.php', 'index.php'), '', str_replace($_SERVER['DOCUMENT_ROOT'], '', $_SERVER['SCRIPT_FILENAME'])) . '(.*)';

		$this->error = true;

		// Check if caching server is online
	    if (!$hostname = trim(@file_get_contents('/etc/sgcache_ip', true)))
	     {
			$this->setError('SG Cache: Connection to cache server failed!');

		    return;
		}

	if (!$cacheServerSocket = @fsockopen($hostname, 80, $errno, $errstr, 2))
		{
  		$this->setError('SG Cache: Connection to cache server failed!');

	  	return;
		}

		$request = "BAN {$purgeRequest} HTTP/1.0\r\nHost: {$_SERVER['SERVER_NAME']}\r\nConnection: Close\r\n\r\n";

		if (preg_match('/^www\./', $_SERVER['SERVER_NAME']))
	    {
			$domain_no_www = preg_replace('/^www\./', '', $_SERVER['SERVER_NAME']);
     	    $request2 = "BAN {$purgeRequest} HTTP/1.0\r\nHost: {$domain_no_www}\r\nConnection: Close\r\n\r\n";
		}
	else
		{
  		$request2 = "BAN {$purgeRequest} HTTP/1.0\r\nHost: www.{$_SERVER['SERVER_NAME']}\r\nConnection: Close\r\n\r\n";
	  }

		fwrite($cacheServerSocket, $request);
		$response = fgets($cacheServerSocket);
		fclose($cacheServerSocket);

		$cacheServerSocket = fsockopen($hostname, 80, $errno, $errstr, 2);
    	fwrite($cacheServerSocket, $request2);
     	fclose($cacheServerSocket);

    	if (!preg_match('/200/', $response))
		{
		$this->setError('SG Cache: Purge was not successful!');
		}
	}
}