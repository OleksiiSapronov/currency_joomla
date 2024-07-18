<?php
/**
 * Plugin Helper File: JRE Cache
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

class plgSystemCacheCleanerHelperJRE extends plgSystemCacheCleanerHelperCache
{
	public function purge()
	{
		$this->emptyTable('#__jrecache_repository');
	}
}
