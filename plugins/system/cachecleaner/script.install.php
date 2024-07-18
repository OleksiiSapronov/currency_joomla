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

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Filesystem\File as JFile;
use Joomla\CMS\Filesystem\Folder as JFolder;

class PlgSystemCacheCleanerInstallerScript
{
    public function postflight($install_type, $adapter)
    {
        if ( ! in_array($install_type, ['install', 'update']))
        {
            return true;
        }

        self::deleteOldFiles();
        self::setCorrectCloudFlareMethod();

        return true;
    }

    private static function delete($files = [])
    {
        foreach ($files as $file)
        {
            if (is_dir($file))
            {
                JFolder::delete($file);
            }

            if (is_file($file))
            {
                JFile::delete($file);
            }
        }
    }

    private static function deleteOldFiles()
    {
        self::delete([
            JPATH_SITE . '/plugins/system/cachecleaner/Cache/MaxCDN.php',
            JPATH_SITE . '/plugins/system/cachecleaner/Api/NetDNA.php',
            JPATH_SITE . '/plugins/system/cachecleaner/Api/OAuth',
        ]);
    }

    private static function setCorrectCloudFlareMethod()
    {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select('params')
            ->from('#__extensions')
            ->where($db->quoteName('element') . ' = ' . $db->quote('cachecleaner'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'));
        $db->setQuery($query);

        $params = $db->loadResult();

        $params = json_decode($params);

        // return if the new param key is found
        if (isset($params->clean_cloudflare_authorization_method))
        {
            return;
        }

        // return if the cloudflare username is not in use
        if (empty($params->cloudflare_username))
        {
            return;
        }

        $params->clean_cloudflare_authorization_method = 'username';

        $query = $db->getQuery(true)
            ->update('#__extensions')
            ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
            ->where($db->quoteName('element') . ' = ' . $db->quote('cachecleaner'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'));
        $db->setQuery($query);
        $db->execute();
    }
}
