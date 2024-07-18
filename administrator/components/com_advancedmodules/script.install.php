<?php
/**
 * @package         Advanced Module Manager
 * @version         9.9.0PRO
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

class Com_AdvancedModulesInstallerScript
{
    public function postflight($install_type, $adapter)
    {
        if ( ! in_array($install_type, ['install', 'update']))
        {
            return true;
        }

        self::createTable();
        self::fixAssignments();
        self::fixAssetIdField();
        self::fixMirrorIdField();
        self::fixCategoryField();
        self::removeAdminMenu();
        self::removeFrontendComponentFromDB();
        self::deleteOldFiles();
        self::fixAssetsRules();

        return true;
    }

    private static function createTable()
    {
        $db = JFactory::getDbo();

        // main table
        $query = "CREATE TABLE IF NOT EXISTS `#__advancedmodules` (
            `moduleid` INT UNSIGNED NOT NULL DEFAULT '0',
            `asset_id` INT UNSIGNED NOT NULL DEFAULT '0',
            `mirror_id` INT NOT NULL DEFAULT '0',
            `category` VARCHAR(50) NOT NULL,
            `params` TEXT NOT NULL,
            PRIMARY KEY (`moduleid`)
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
                JPATH_ADMINISTRATOR . '/components/com_advancedmodules/script.advancedmodules.php',
                JPATH_SITE . '/components/com_advancedmodules/advancedmodules.xml',
                JPATH_SITE . '/components/com_advancedmodules/script.advancedmodules.php',
                JPATH_SITE . '/plugins/system/advancedmodules/modulehelper.php',
            ]
        );
    }

    private static function fixAssetIdField()
    {
        $db = JFactory::getDbo();

        // add asset_id column
        $query = "SHOW COLUMNS FROM `" . $db->getPrefix() . "advancedmodules` LIKE 'asset_id'";
        $db->setQuery($query);
        $has_field = $db->loadResult();

        if ($has_field)
        {
            return;
        }

        $query = "ALTER TABLE `#__advancedmodules` ADD `asset_id` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `moduleid`";
        $db->setQuery($query);
        $db->execute();
    }

    private static function fixAssetsRules()
    {
        $db = JFactory::getDbo();

        // Remove unused assets entry (uses com_modules)
        $query = $db->getQuery(true)
            ->delete('#__assets')
            ->where('name LIKE ' . $db->quote('com_advancedmodules.module.%'));
        $db->setQuery($query);
        $db->execute();
    }

    private static function fixAssignments()
    {
        self::fixAssignmentsRemoveInitialAssignments();
        self::fixAssignmentsCorrectOldKeys();
    }

    private static function fixAssignmentsCorrectOldKeys()
    {
        $db = JFactory::getDbo();

        // correct old keys and values
        $query = $db->getQuery(true)
            ->select($db->quoteName('moduleid', 'id'))
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__advancedmodules'));
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        foreach ($rows as $row)
        {
            if (empty($row->params))
            {
                continue;
            }

            if ($row->params[0] != '{')
            {
                $row->params = str_replace('assignto_secscats', 'assignto_cats', $row->params);
                $row->params = str_replace('flexicontent', 'fc', $row->params);

                $params = JRegistryFormat::getInstance('INI')->stringToObject($row->params);
            }
            else
            {
                $params = json_decode($row->params);

                if (is_null($params))
                {
                    $params = (object) [];
                }
            }

            // move tooltip to notes field
            if ( ! empty($params->tooltip))
            {
                $query->clear()
                    ->update($db->quoteName('#__modules'))
                    ->set($db->quoteName('note') . ' = ' . $db->quote($params->tooltip))
                    ->where($db->quoteName('id') . ' = ' . (int) $row->id);
                $db->setQuery($query);
                $db->execute();
                unset($params->tooltip);
            }

            // concatenate sef and non-sef url fields
            if (isset($params->assignto_urls_selection_sef))
            {
                $params->assignto_urls_selection = trim($params->assignto_urls_selection . "\n" . $params->assignto_urls_selection_sef);
                unset($params->assignto_urls_selection_sef);
                unset($params->show_url_field);
            }

            // set urls_regex value if assignto_urls is used
            if ( ! empty($params->assignto_urls) && ! isset($params->assignto_urls_regex))
            {
                $params->assignto_urls_regex = 1;
            }

            foreach ($params as $k => &$v)
            {
                switch ($k)
                {
                    case 'assignto_php_selection':
                    case 'assignto_urls_selection':
                    case 'assignto_ips_selection':
                        $v = str_replace(['\n', '\|'], ["\n", '|'], $v);
                        break;
                    case 'color':
                        $v = str_replace('#', '', $v);
                        $v = (empty($v) || $v == 'none') ? 'none' : $v;

                        if ($v && $v != 'none')
                        {
                            $v = '#' . strtolower($v);
                        }
                        break;
                    case 'assignto_users_selection':
                        if ( ! is_array($v))
                        {
                            $v = explode('|', $v);
                        }
                        break;
                    default:
                        if (
                            (substr($k, -10) == '_selection' || substr($k, -4) == '_inc')
                            && ! is_array($v)
                        )
                        {
                            // convert | separated strings to arrays
                            $v = explode('|', $v);
                        }
                        break;
                }
            }

            if ( ! empty($params->assignto_cats_selection))
            {
                foreach ($params->assignto_cats_selection as $key => $val)
                {
                    if (strpos($val, ':') !== false)
                    {
                        $params->assignto_cats_selection[$key] = substr($val, strpos($val, ':') + 1);
                    }
                }
            }

            $params = json_encode($params);

            if ($params == $row->params)
            {
                continue;
            }

            $query->clear()
                ->update($db->quoteName('#__advancedmodules'))
                ->set($db->quoteName('params') . ' = ' . $db->quote($params))
                ->where($db->quoteName('moduleid') . ' = ' . (int) $row->id);
            $db->setQuery($query);
            $db->execute();
        }
    }

    private static function fixAssignmentsRemoveInitialAssignments()
    {
        $db = JFactory::getDbo();

        // remove initial menu assignment settings
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__advancedmodules'))
            ->set($db->quoteName('params') . ' = ' . $db->quote(''))
            ->where($db->quoteName('params') . ' = ' . $db->quote('{"assignto_menuitems":0,"assignto_menuitems_selection":[]}'));
        $db->setQuery($query);
        $db->execute();
    }

    private static function fixCategoryField()
    {
        $db = JFactory::getDbo();

        // add asset_id column
        $query = "SHOW COLUMNS FROM `" . $db->getPrefix() . "advancedmodules` LIKE 'category'";
        $db->setQuery($query);
        $has_field = $db->loadResult();

        if ($has_field)
        {
            return;
        }

        $query = "ALTER TABLE `#__advancedmodules` ADD COLUMN `category` VARCHAR(50) NOT NULL AFTER `mirror_id`";
        $db->setQuery($query);
        $db->execute();
    }

    private static function fixMirrorIdField()
    {
        $db = JFactory::getDbo();

        // add mirror_id column
        $query = "SHOW COLUMNS FROM `" . $db->getPrefix() . "advancedmodules` LIKE 'mirror_id'";
        $db->setQuery($query);
        $has_field = $db->loadResult();

        if ($has_field)
        {
            return;
        }

        $query = "ALTER TABLE `#__advancedmodules` ADD `mirror_id` INT NOT NULL DEFAULT '0' AFTER `asset_id`";
        $db->setQuery($query);
        $db->execute();

        self::fixMirrorIdFieldFixParams();
    }

    private static function fixMirrorIdFieldFixParams()
    {
        $db = JFactory::getDbo();

        // correct old keys and values
        $query = $db->getQuery(true)
            ->select($db->quoteName('moduleid', 'id'))
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__advancedmodules'));
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        foreach ($rows as $row)
        {
            if (empty($row->params))
            {
                continue;
            }

            $params = json_decode($row->params);

            if (is_null($params))
            {
                continue;
            }

            // set urls_regex value if assignto_urls is used
            if (empty($params->mirror_module) || empty($params->mirror_moduleid))
            {
                continue;
            }

            $mirror_id = $params->mirror_moduleid;
            unset($params->mirror_module);
            unset($params->mirror_moduleid);

            $query->clear()
                ->update($db->quoteName('#__advancedmodules'))
                ->set($db->quoteName('mirror_id') . ' = ' . (int) $mirror_id)
                ->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)))
                ->where($db->quoteName('moduleid') . ' = ' . (int) $row->id);
            $db->setQuery($query);
            $db->execute();
        }
    }

    private static function removeAdminMenu()
    {
        $db = JFactory::getDbo();

        // hide admin menu
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__menu'))
            ->where($db->quoteName('path') . ' = ' . $db->quote('advancedmodules'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
            ->where($db->quoteName('client_id') . ' = 1');
        $db->setQuery($query);
        $db->execute();
    }

    private static function removeFrontendComponentFromDB()
    {
        $db = JFactory::getDbo();

        // remove frontend component from extensions table
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_advancedmodules'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'))
            ->where($db->quoteName('client_id') . ' = 0');
        $db->setQuery($query);
        $db->execute();

        JFactory::getCache()->clean('_system');
    }
}
