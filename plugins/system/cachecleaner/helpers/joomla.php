<?php
/**
 * Plugin Helper File: Joomla cache
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

class plgSystemCacheCleanerHelperJoomla extends plgSystemCacheCleanerHelperCache
{
	public function purge()
	{
		$this->emptyFolder(JPATH_SITE . '/cache');
		$this->emptyFolder(JPATH_ADMINISTRATOR . '/cache');

		$cache = $this->getCache();
		if(isset($cache->options['storage']) && $cache->options['storage'] != 'file')
		{
			$cache->clean(null, 'all');
		}
	}

	public function purgeExpired()
	{
		$cache = $this->getCache();
		$cache->gc();
	}

	public function purgeUpdates()
	{
		$db = JFactory::getDBO();
		$db->setQuery('TRUNCATE TABLE #__updates');
		if (!$db->execute())
		{
			return;
		}

		// Reset the last update check timestamp
		$query = $db->getQuery(true)
			->update('#__update_sites')
			->set('last_check_timestamp = ' . $db->quote(0));
		$db->setQuery($query);
		$db->execute();
	}

	public function checkIn()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$nullDate = $db->getNullDate();

		$tables = $db->getTableList();

		foreach ($tables as $table)
		{
			// make sure we get the right tables based on prefix
			if (strpos($table, $db->getPrefix()) !== 0)
			{
				continue;
			}

			$fields = $db->getTableColumns($table);

			if (!(isset($fields['checked_out']) && isset($fields['checked_out_time'])))
			{
				continue;
			}

			$query->clear()
				->update($db->quoteName($table))
				->set('checked_out = 0')
				->set('checked_out_time = ' . $db->quote($nullDate))
				->where('checked_out > 0');
			if (isset($fields['editor']))
			{
				$query->set('editor = NULL');
			}
			$db->setQuery($query);
			$db->execute();
		}
	}

	private function getCache()
	{
		$conf = JFactory::getConfig();

		$options = array(
			'defaultgroup' => '',
			'storage'      => $conf->get('cache_handler', ''),
			'caching'      => true,
			'cachebase'    => $conf->get('cache_path', JPATH_SITE . '/cache')
		);

		$cache = JCache::getInstance('', $options);

		return $cache;
	}
}
