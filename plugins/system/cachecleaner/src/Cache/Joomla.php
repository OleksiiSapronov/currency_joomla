<?php
/**
 * @package         Cache Cleaner
 * @version         8.5.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

namespace RegularLabs\Plugin\System\CacheCleaner\Cache;

defined('_JEXEC') or die;

use Joomla\CMS\Cache\Cache as JCache;
use Joomla\CMS\Cache\CacheController;
use Joomla\CMS\Factory as JFactory;
use RegularLabs\Plugin\System\CacheCleaner\Params;

class Joomla extends Cache
{
    public static function checkIn()
    {
        $db       = JFactory::getDbo();
        $query    = $db->getQuery(true);
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

            if ( ! (isset($fields['checked_out']) && isset($fields['checked_out_time'])))
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

    public static function purge()
    {
        $cache = self::getCache();

        if (isset($cache->options['storage']) && $cache->options['storage'] != 'file')
        {
            foreach ($cache->getAll() as $group)
            {
                $cache->clean($group->group);
            }

            return;
        }

        $cache_path = JFactory::getConfig()->get('cache_path', JPATH_SITE . '/cache');

        $min_age = Params::get()->clean_cache_min_age;

        self::emptyFolder($cache_path, $min_age);
        self::emptyFolder(JPATH_ADMINISTRATOR . '/cache', $min_age);
    }

    public static function purgeExpired()
    {
        $min_age = JFactory::getConfig()->get('cachetime');

        if ( ! $min_age)
        {
            return;
        }

        $cache_path = JFactory::getConfig()->get('cache_path', JPATH_SITE . '/cache');

        self::emptyFolder($cache_path, $min_age);
    }

    public static function purgeLiteSpeed()
    {
        header('X-LiteSpeed-Purge: *');
    }

    public static function purgeOPcache()
    {
        if (
            function_exists('opcache_reset')
            && function_exists('opcache_get_status')
        )
        {
            $status = @opcache_get_status();

            if ( ! empty($status['opcache_enabled']))
            {
                @opcache_reset();

                return;
            }
        }

        if (function_exists('apc_clear_cache'))
        {
            @apc_clear_cache();

            return;
        }
    }

    public static function purgeUpdates()
    {
        $db = JFactory::getDbo();
        $db->setQuery('TRUNCATE TABLE #__updates');

        if ( ! $db->execute())
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

    /**
     * @return CacheController
     */
    private static function getCache()
    {
        $conf = JFactory::getConfig();

        $options = [
            'defaultgroup' => '',
            'storage'      => $conf->get('cache_handler', ''),
            'caching'      => true,
            'cachebase'    => $conf->get('cache_path', JPATH_SITE . '/cache'),
        ];

        $cache = JCache::getInstance('callback', $options);

        return $cache;
    }
}
