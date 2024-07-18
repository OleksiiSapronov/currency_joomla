<?php
/**
 * Plugin Helper File: Tables
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

class plgSystemCacheCleanerHelperTables extends plgSystemCacheCleanerHelperCache
{
	public function purge($tables = array())
	{
		if (empty($tables))
		{
			return;
		}

		if (!is_array($tables))
		{
			$tables = explode(',', str_replace("\n", ',', $tables));
		}

		foreach ($tables as $table)
		{
			$this->emptyTable($table);
		}
	}
}
