<?php
/**
 * @package         ReReplacer
 * @version         13.2.0PRO
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
use Joomla\Registry\AbstractRegistryFormat as JRegistryFormat;

class Com_ReReplacerInstallerScript
{
    public function postflight($install_type, $adapter)
    {
        if ( ! in_array($install_type, ['install', 'update']))
        {
            return true;
        }

        self::createTable();
        self::fixOldFormatInDatabase();
        self::deleteOldFiles();

        return true;
    }

    private static function createTable()
    {
        $db = JFactory::getDbo();

        $query = "CREATE TABLE IF NOT EXISTS `#__rereplacer` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `name` VARCHAR(100) NOT NULL,
            `description` TEXT NOT NULL,
            `category` VARCHAR(50) NOT NULL,
            `search` TEXT NOT NULL,
            `replace` TEXT NOT NULL,
            `area` TEXT NOT NULL,
            `params` TEXT NOT NULL,
            `published` TINYINT(1) NOT NULL DEFAULT '0',
            `ordering` INT NOT NULL DEFAULT '0',
            `checked_out` INT UNSIGNED NOT NULL DEFAULT '0',
            `checked_out_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY  (`id`),
            KEY `id` (`id`,`published`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        $db->setQuery($query);
        $db->execute();
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
        self::delete(
            [
                JPATH_SITE . '/components/com_rereplacer',
            ]
        );
    }

    private static function fixOldFormatInDatabase()
    {
        $db = JFactory::getDbo();

        $query = 'SHOW FIELDS FROM ' . $db->quoteName('#__rereplacer');
        $db->setQuery($query);
        $columns = $db->loadColumn();

        if ( ! in_array('category', $columns))
        {
            $query = 'ALTER TABLE ' . $db->quoteName('#__rereplacer')
                . ' CHANGE COLUMN `name` `name` VARCHAR(100) NOT NULL AFTER `id`,'
                . ' ADD COLUMN `category` VARCHAR(50) NOT NULL AFTER `description`';
            $db->setQuery($query);
            $db->query();
        }

        // convert old J1.5 params syntax to new
        $query = $db->getQuery(true);
        $query->select('r.id, r.params')
            ->from('#__rereplacer as r')
            ->where('r.params REGEXP ' . $db->quote('^[^\{]'));
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        foreach ($rows as $row)
        {
            if (empty($row->params))
            {
                continue;
            }

            $row->params = str_replace('assignto_secscats', 'assignto_cats', $row->params);
            $row->params = str_replace('flexicontent', 'fc', $row->params);

            $params = JRegistryFormat::getInstance('INI')->stringToObject($row->params);

            foreach ($params as $key => $val)
            {
                if (is_string($val) && ! (strpos($val, '|') === false))
                {
                    $params->{$key} = explode('|', $val);
                }
            }

            if ( ! empty($params->assignto_cats_selection))
            {
                foreach ($params->assignto_cats_selection as $key => $val)
                {
                    if ( ! (strpos($val, ':') === false))
                    {
                        $params->assignto_cats_selection[$key] = substr($val, strpos($val, ':') + 1);
                    }
                }
            }

            $query = $db->getQuery(true);
            $query->update('#__rereplacer as r')
                ->set('r.params = ' . $db->quote(json_encode($params)))
                ->where('r.id = ' . (int) $row->id);
            $db->setQuery($query);
            $db->execute();
        }

        // concatenates the sef and non-sef url fields
        $query = $db->getQuery(true);
        $query->update('#__rereplacer as r')
            ->set(
                'r.params = replace( replace( replace( replace( `params`,'
                . $db->quote('"assignto_urls_selection_sef"') . ',' . $db->quote('"assignto_urls_selection"') . '),'
                . $db->quote('"assignto_urls_selection":"","assignto_browsers"') . ',' . $db->quote('"assignto_browsers"') . '),'
                . $db->quote('","show_url_field":"0","assignto_urls_selection":"') . ',' . $db->quote('\n') . '),'
                . $db->quote('","show_url_field":"1","assignto_urls_selection":"') . ',' . $db->quote('\n') . ')'
            )
            ->where('r.params LIKE ' . $db->quote('%"assignto_urls_selection_sef"%'));
        $db->setQuery($query);
        $db->execute();

        // add url_regex value to filled in url fields
        $query = $db->getQuery(true);
        $query->update('#__rereplacer as r')
            ->set(
                'r.params = replace( replace( replace( replace( `params`,'
                . $db->quote('"assignto_os"') . ',' . $db->quote('"assignto_urls_regex":"1","assignto_os"') . '),'
                . $db->quote('"","assignto_urls_regex":"1"') . ',' . $db->quote('""') . '),'
                . $db->quote('"assignto_urls_regex":"0","assignto_urls_regex":"1"') . ',' . $db->quote('"assignto_urls_regex":"0"') . '),'
                . $db->quote('"assignto_urls_regex":"1","assignto_urls_regex":"1"') . ',' . $db->quote('"assignto_urls_regex":"1"') . ')'
            )
            ->where('r.params LIKE ' . $db->quote('%"assignto_urls":"1"%'))
            ->where('r.params NOT LIKE ' . $db->quote('%"assignto_urls_regex"%'));
        $db->setQuery($query);
        $db->execute();
    }
}
