<?php
/**
 * Plugin Helper File: JotCache
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

class plgSystemCacheCleanerHelperJotCache extends plgSystemCacheCleanerHelperCache
{
	public function purge()
	{
		jimport('joomla.filesystem.file');
		$file = JPATH_ADMINISTRATOR . '/components/com_jotcache/models/main.php';

		if (!JFile::exists($file))
		{
			return;
		}

		require_once $file;
		require_once __DIR__ . '/jotcachemodel.php';

		$model = new JotCacheMainModelMain;
		$model->deleteall();
	}
}
