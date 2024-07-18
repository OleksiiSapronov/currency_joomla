<?php
/**
 * Plugin Helper File: Folders
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

require_once __DIR__ . '/cache.php';

class plgSystemCacheCleanerHelperFolders extends plgSystemCacheCleanerHelperCache
{
	public function purge()
	{
	  // Empty tmp folder
	  if ($this->params->clean_tmp)
		{
	    	$this->emptyFolder(JPATH_SITE . '/tmp');
	     }

		// Empty custom folder
    	if ($this->params->clean_folders)
		{
		$folders = explode("\n", str_replace('\n', "\n", $this->params->clean_folders));
	  	foreach ($folders as $folder)
			{
			  if (!trim($folder))
				{
		    		continue;
     			}

	    		$folder = rtrim(str_replace('\\', '/', trim($folder)), '/');
			$path = str_replace('//', '/', JPATH_SITE . '/' . $folder);
  			$this->emptyFolder($path);
			}
	  }
	}
}