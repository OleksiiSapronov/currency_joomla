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

use Joomla\CMS\Table\Table as JTable;

class AdvancedModulesTable extends JTable
{
    public function __construct(&$db)
    {
        parent::__construct('#__advancedmodules', 'moduleid', $db);
    }

    protected function _getAssetName()
    {
        $k = $this->_tbl_key;

        return 'com_modules.module.' . (int) $this->{$k};
    }

    /**
     * Method to get the parent asset id for the record
     *
     * @param JTable  $table A JTable object for the asset parent
     * @param integer $id
     *
     * @return  integer
     */
    protected function _getAssetParentId(JTable $table = null, $id = null)
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__assets')
            ->where('name = ' . $db->quote('com_modules'));
        $db->setQuery($query);
        $assetId = $db->loadResult();

        return $assetId ?: parent::_getAssetParentId($table, $id);
    }

    protected function _getAssetTitle()
    {
        if (isset($this->_title))
        {
            return $this->_title;
        }

        $k = (int) $this->_tbl_key;

        if (empty($this->{$k}))
        {
            return parent::_getAssetTitle();
        }

        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select('title')
            ->from('#__modules')
            ->where('id = ' . (int) $this->{$k});
        $db->setQuery($query);

        return $db->loadResult();
    }
}

class AdvancedModulesTableAdvancedModules extends AdvancedModulesTable
{
}
