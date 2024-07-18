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

/**
 * Based on:
 * Siteground Joomla Cache Plugin (jSGCache)
 *
 * @author       George Penkov
 * @category     Siteground Joomla Plugins
 * @package      Siteground Joomla Cache Plugin
 */
class SiteGround extends Cache
{
    public static function purge()
    {
        $domain_no_www = preg_replace('/^www\./', '', $_SERVER['SERVER_NAME']);
        $response      = shell_exec('curl -sX PURGE http://127.0.0.1/* -H "Host:' . $domain_no_www . '" --tlsv1.2');

        if (strpos($response, 'Successful purge') === false)
        {
            self::addError('SiteGround Cache: Purge was not successful!');

            return false;
        }

        return true;
    }
}
