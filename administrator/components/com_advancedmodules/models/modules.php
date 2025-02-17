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

/**
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper as JComponentHelper;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Multilanguage as JLanguageMultilang;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\MVC\Model\ListModel as JModelList;
use Joomla\CMS\Router\Route as JRoute;
use Joomla\Utilities\ArrayHelper as JArrayHelper;
use RegularLabs\Library\ParametersNew as RL_Parameters;

/**
 * Modules Component Module Model
 */
class AdvancedModulesModelModules extends JModelList
{
    /**
     * Constructor.
     *
     * @param array $config An optional associative array of configuration settings.
     *
     * @see     JController
     * @since   1.6
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = [
                'id', 'a.id',
                'color', 'a.color',
                'category', 'aa.category',
                'title', 'a.title',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'published', 'a.published', 'state',
                'access', 'a.access',
                'ag.title', 'access_level',
                'ordering', 'a.ordering',
                'module', 'a.module',
                'a.language',
                'l.title', 'language_title',
                'publish_up', 'a.publish_up',
                'publish_down', 'a.publish_down',
                'client_id', 'a.client_id',
                'position', 'a.position',
                'menuitem',
                'menuid',
                'name', 'e.name',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Get the filter form
     *
     * @param array   $data     data
     * @param boolean $loadData load current data
     *
     * @return  JForm|boolean  The \JForm object or false on error
     *
     * @since   3.2
     */
    public function getFilterForm($data = [], $loadData = true)
    {
        $form = parent::getFilterForm($data, $loadData);

        if ( ! ($form instanceof JForm))
        {
            return $form;
        }

        $xml = $form->getXml();

        $config = $this->getConfig();

        if ( ! $config->use_categories)
        {
            [$element] = $xml->xpath('/*/fields/field[@name="category"]');
            unset($element[0]);
        }

        return $form;
    }

    /**
     * Returns an object list
     *
     * @param string $query      The query
     * @param int    $limitstart Offset
     * @param int    $limit      The number of records
     *
     * @return  array
     */
    protected function _getList($query, $limitstart = 0, $limit = 0)
    {
        $ordering  = strtolower($this->getState('list.ordering', 'ordering'));
        $orderDirn = strtoupper($this->getState('list.direction', 'ASC'));

        if (in_array($ordering, ['menuid', 'name']))
        {
            $this->_db->setQuery($query);
            $result = $this->_db->loadObjectList();
            $this->translate($result);

            $result = JArrayHelper::sortObjects($result, $ordering, $orderDirn == 'DESC' ? -1 : 1, true, true);

            $total                                      = count($result);
            $this->cache[$this->getStoreId('getTotal')] = $total;

            if ($total < $limitstart)
            {
                $limitstart = 0;
                $this->setState('list.start', 0);
            }

            return array_slice($result, $limitstart, $limit ?: null);
        }

        if ($ordering == 'color')
        {
            return $this->_getListByColor($query, $limitstart, $limit, $orderDirn);
        }

        if ($ordering == 'ordering')
        {
            $query->order('a.position ASC');
            $ordering = 'a.ordering';
        }

        if ($ordering == 'language_title')
        {
            $ordering = 'l.manifest_cache';
        }

        $query->order($this->_db->quoteName($ordering) . ' ' . $orderDirn);

        if ($ordering == 'position')
        {
            $query->order('a.ordering ASC');
        }

        $result = parent::_getList($query, $limitstart, $limit);
        $this->translate($result);

        return $result;
    }

    protected function _getListByColor($query, $limitstart, $limit, $orderDirn)
    {
        $query->order('a.title' . ' ' . $orderDirn);
        $this->_db->setQuery($query);

        $result = $this->_db->loadObjectList();
        $this->translate($result);

        $newresult = [];

        $config = $this->getConfig();

        $colors = str_replace('#', '', strtolower($config->main_colors));
        $colors = explode(',', $colors);
        $colors = array_diff($colors, ['none']);

        foreach ($result as $i => $row)
        {
            $params = json_decode($row->advancedparams);

            if (is_null($params))
            {
                $params = (object) [];
            }

            $color = str_replace('#', '', $params->color ?? 'none');

            $pos = array_search(strtolower($color), $colors);
            $pos = $pos === false ? 'none' : str_pad($pos, 4, '0', STR_PAD_LEFT);

            $newresult[$pos . '.' . str_pad($i, 8, '0', STR_PAD_LEFT)] = $row;
        }

        ksort($newresult);

        if ($orderDirn == 'DESC')
        {
            krsort($newresult);
        }

        $newresult = array_values($newresult);
        $total     = count($newresult);

        $this->cache[$this->getStoreId('getTotal')] = $total;

        if ($total < $limitstart)
        {
            $limitstart = 0;
            $this->setState('list.start', 0);
        }

        return array_slice($newresult, $limitstart, $limit ?: null);
    }

    /**
     * Function that gets the config settings
     *
     * @return    Object
     */
    protected function getConfig()
    {
        if (isset($this->config))
        {
            return $this->config;
        }

        $this->config = RL_Parameters::getComponent('advancedmodules');

        return $this->config;
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  JDatabaseQuery
     */
    protected function getListQuery()
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            // Select the required fields from the table.
            ->select('a.id')
            ->from($db->quoteName('#__modules', 'a'))
            // Join over the module menus
            ->join('LEFT', $db->quoteName('#__modules_menu', 'mm') . ' ON ' . $db->quoteName('mm.moduleid') . ' = ' . $db->quoteName('a.id'))
            // Join over the extensions
            ->join('LEFT', $db->quoteName('#__extensions', 'e') . ' ON ' . $db->quoteName('e.element') . ' = ' . $db->quoteName('a.module'))
            // Join advanced params
            ->join('LEFT', $db->quoteName('#__advancedmodules', 'aa') . ' ON ' . $db->quoteName('aa.moduleid') . ' = ' . $db->quoteName('a.id'));

        // Filter by module
        $module = $this->getState('filter.module');

        if ($module)
        {
            $query->where($db->quoteName('a.module') . ' = ' . $db->quote($module));
        }

        // Filter by category
        $category = $this->getState('filter.category');

        if ($category)
        {
            $query->where($db->quoteName('aa.category') . ' = ' . $db->quote($category));
        }

        $wheres = [];

        // Filter by menuid
        $menuid = $this->getState('filter.menuid');

        switch ($menuid)
        {
            case '':
                break;
            case '0':
                $wheres[] = $db->quoteName('mm.menuid') . ' = 0';
                break;
            case '-':
                $wheres[] = $db->quoteName('mm.menuid') . ' IS NULL';
                break;
            case '-1':
                $wheres[] = $db->quoteName('mm.menuid') . ' LIKE \'-%\'';
                break;
            case '-2':
                $wheres[] = $db->quoteName('mm.menuid') . ' NOT LIKE \'-%\' AND ' . $db->quoteName('mm.menuid') . ' != 0';
                break;
            default:
                $wheres[] = '(' . $db->quoteName('mm.menuid') . ' IN (0, ' . (int) $menuid . ')'
                    . ' OR (' . $db->quoteName('mm.menuid') . ' LIKE \'-%\' AND ' . $db->quoteName('mm.menuid') . ' != ' . $db->quote('-' . (int) $menuid) . '))';
                break;
        }

        // Filter by position
        $position = $this->getState('filter.position');

        if ($position)
        {
            $wheres[] = $db->quoteName('a.position') . ' = ' . $db->quote($position != 'none' ? $position : '');
        }

        // Filter by current user access level.
        $user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

        // Get the current user for authorisation checks
        if ($user->authorise('core.admin') !== true)
        {
            $groups   = implode(',', $user->getAuthorisedViewLevels());
            $wheres[] = 'a.access IN (' . $groups . ')';
        }

        // Filter by access level.
        $access = $this->getState('filter.access');

        if ($access)
        {
            $wheres[] = 'a.access = ' . (int) $access;
        }

        // Filter on the language.
        $language = $this->getState('filter.language');

        if ($language)
        {
            $wheres[] = $db->quoteName('a.language') . ' = ' . $db->quote($language);
        }

        // Filter by published state
        $published = $this->getState('filter.state');

        // Modal view should return only front end modules
        if (JFactory::getApplication()->input->get('layout') == 'modal')
        {
            $published = 1;
        }

        if (is_numeric($published))
        {
            $wheres[] = $db->quoteName('a.published') . ' = ' . (int) $published;
        }
        elseif ($published == '')
        {
            $wheres[] = '(' . $db->quoteName('a.published') . ' IN (0, 1))';
        }

        // Filter by client.
        $clientId = $this->getState('filter.client_id');

        // Modal view should return only front end modules
        if (JFactory::getApplication()->input->get('layout') == 'modal')
        {
            $clientId = 0;
        }

        if (is_numeric($clientId))
        {
            $wheres[] = $db->quoteName('a.client_id') . ' = ' . (int) $clientId
                . ' AND ' . $db->quoteName('e.client_id') . ' =' . (int) $clientId;
        }

        $language = $this->getState('filter.language');

        // Modal view should return only specific language and ALL
        if (JFactory::getApplication()->input->get('layout') == 'modal')
        {
            if (JFactory::getApplication()->isClient('site') && JLanguageMultilang::isEnabled())
            {
                $query->where($db->quoteName('a.language') . ' in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
            }
        }
        elseif ($language)
        {
            // Filter on the language.
            $query->where($db->quoteName('a.language') . ' = ' . $db->quote($language));
        }

        // Set wheres
        foreach ($wheres as $where)
        {
            $query->where($where);
        }

        // Filter by search in title
        $search = trim($this->getState('filter.search'));

        if ( ! empty($search))
        {
            if (stripos($search, 'id:') === 0)
            {
                $query->where($db->quoteName('a.id') . ' = ' . (int) substr($search, 3));
            }
            else
            {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                $query->where('('
                    . $db->quoteName('a.title') . ' LIKE ' . $search
                    . ' OR ' . $db->quoteName('a.note') . ' LIKE ' . $search
                    . ')');
            }
        }

        $db->setQuery($query);
        $ids  = $db->loadColumn();
        $user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

        foreach ($ids as $key => $id)
        {
            if ($user->authorise('core.edit', 'com_modules.module.' . $id))
            {
                continue;
            }

            unset($ids[$key]);
        }

        if ( ! empty($ids))
        {
            $mirror_wheres = $wheres;
            array_unshift(
                $mirror_wheres,
                $db->quoteName('aa.mirror_id') . ' IN (' . implode(',', $ids) . ',-' . implode(',-', $ids) . ')'
            );

            $query->clear('where');

            // Set wheres
            foreach ($mirror_wheres as $where)
            {
                $query->where($where);
            }

            $db->setQuery($query);
            $mirror_ids = $db->loadColumn();

            $mirror_ids = array_unique($mirror_ids);

            foreach ($mirror_ids as $key => $id)
            {
                if ($user->authorise('core.edit', 'com_modules.module.' . $id))
                {
                    continue;
                }

                unset($mirror_ids[$key]);
            }

            $ids = array_merge($ids, $mirror_ids);
        }

        $query = $db->getQuery(true)
            // Select the required fields from the table.
            ->select(
                $this->getState(
                    'list.select',
                    'a.id, a.title, a.note, a.position, a.module, a.language,' .
                    'a.checked_out, a.checked_out_time, a.published as published, e.enabled as enabled, a.access, a.ordering, a.publish_up, a.publish_down'
                )
            )
            ->from('#__modules AS a')
            // Join over the language
            ->select($db->quoteName('l.title', 'language_title'))
            ->select($db->quoteName('l.image', 'language_image'))
            ->join('LEFT', $db->quoteName('#__languages', 'l') . ' ON ' . $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.language'))
            ->select($db->quoteName('le.element', 'language'))
            ->join('LEFT', $db->quoteName('#__extensions', 'le') . ' ON ' . $db->quoteName('le.element') . ' = ' . $db->quoteName('a.language'))
            // Join over the users for the checked out user.
            ->select($db->quoteName('uc.name', 'editor'))
            ->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('a.checked_out'))
            // Join over the asset groups.
            ->select($db->quoteName('ag.title', 'access_level'))
            ->join('LEFT', $db->quoteName('#__viewlevels', 'ag') . ' ON ' . $db->quoteName('ag.id') . ' = ' . $db->quoteName('a.access'))
            // Join over the module menus
            ->select('MIN(mm.menuid) AS menuid')
            ->join('LEFT', $db->quoteName('#__modules_menu', 'mm') . ' ON ' . $db->quoteName('mm.moduleid') . ' = ' . $db->quoteName('a.id'))
            // Join over the extensions
            ->select($db->quoteName('e.name', 'name'))
            ->join('LEFT', $db->quoteName('#__extensions', 'e') . ' ON ' . $db->quoteName('e.element') . ' = ' . $db->quoteName('a.module'))
            // Join over the advanced params
            ->select($db->quoteName('aa.params', 'advancedparams'))
            ->select($db->quoteName('aa.mirror_id', 'mirror_id'))
            ->select($db->quoteName('aa.category', 'category'))
            ->join('LEFT', $db->quoteName('#__advancedmodules', 'aa') . ' ON ' . $db->quoteName('aa.moduleid') . ' = ' . $db->quoteName('a.id'))
            // Group
            ->group('a.id');

        if (empty($ids))
        {
            $query->where('1 = 0');

            return $query;
        }

        $ids = array_unique($ids);

        $query->where($db->quoteName('a.id') . ' IN (' . implode(',', $ids) . ')');

        return $query;
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param string $id A prefix for the store id.
     *
     * @return  string    A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . trim($this->getState('filter.search'));
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.state');
        $id .= ':' . $this->getState('filter.position');
        $id .= ':' . $this->getState('filter.module');
        $id .= ':' . $this->getState('filter.category');
        $id .= ':' . $this->getState('filter.menuid');
        $id .= ':' . $this->getState('filter.client_id');
        $id .= ':' . $this->getState('filter.language');

        return parent::getStoreId($id);
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState($this->context, new stdClass);

        // Pre-fill the list options
        if ( ! property_exists($data, 'list'))
        {
            $config = $this->getConfig();
            [$default_ordering, $default_direction] = explode(' ', $config->default_ordering, 2);

            $data->list = [
                'direction'    => $default_direction,
                'limit'        => $this->state->{'list.limit'},
                'ordering'     => $default_ordering,
                'fullordering' => $config->default_ordering,
                'start'        => $this->state->{'list.start'},
            ];
        }

        return $data;
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param string $ordering  An optional ordering field.
     * @param string $direction An optional direction (asc|desc).
     *
     * @return  void
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = JFactory::getApplication('administrator');

        // Load the filter state.
        $search = trim($this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search'));
        $this->setState('filter.search', $search);

        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
        $this->setState('filter.access', $access);

        $state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string');
        $this->setState('filter.state', $state);

        $position = $this->getUserStateFromRequest($this->context . '.filter.position', 'filter_position', '', 'string');
        $this->setState('filter.position', $position);

        $module = $this->getUserStateFromRequest($this->context . '.filter.module', 'filter_module', '', 'string');
        $this->setState('filter.module', $module);

        $category = $this->getUserStateFromRequest($this->context . '.filter.category', 'filter_category', '', 'string');
        $this->setState('filter.category', $category);

        // Special handling for filter client_id.

        // Try to get current Client selection from $_POST.
        $clientId = $app->input->getString('client_id', null);

        // Client Site(0) or Administrator(1) selected?
        if (in_array($clientId, ['0', '1']))
        {
            // Not the same client like saved previous one?
            if ($clientId != $app->getUserState($this->context . '.client_id'))
            {
                // Save current selection as new previous value in session.
                $app->setUserState($this->context . '.client_id', $clientId);

                // Reset pagination.
                $app->input->set('limitstart', 0);
            }
        }

        // No Client selected?
        else
        {
            // Try to get previous one from session.
            $clientId = (string) $app->getUserState($this->context . '.client_id');

            // Client not Site(0) and not Administrator(1)? So, set to Site(0).
            if ( ! in_array($clientId, ['0', '1']))
            {
                $clientId = '0';
            }
        }

        // Modal view should return only front end modules
        if (JFactory::getApplication()->input->get('layout') == 'modal')
        {
            $clientId = 0;
        }

        $this->setState('filter.client_id', $clientId);

        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
        $this->setState('filter.language', $language);

        // Load the parameters.
        $params = JComponentHelper::getParams('com_advancedmodules');
        $this->setState('params', $params);

        // List state information.
        $config = $this->getConfig();
        [$default_ordering, $default_direction] = explode(' ', $config->default_ordering, 2);

        $this->setState('list.fullordering', $config->default_ordering);
        parent::populateState($default_ordering, $default_direction);
    }

    /**
     * Translate a list of objects
     *
     * @param array &$items The array of objects
     */
    protected function translate(&$items)
    {
        $lang   = JFactory::getLanguage();
        $client = $this->getState('filter.client_id') ? 'administrator' : 'site';

        foreach ($items as $item)
        {
            $extension = $item->module;
            $source    = constant('JPATH_' . strtoupper($client)) . "/modules/$extension";
            $lang->load("$extension.sys", constant('JPATH_' . strtoupper($client)), null, false, true)
            || $lang->load("$extension.sys", $source, null, false, true);
            $item->name = JText::_($item->name);

            if ($item->mirror_id > 0)
            {
                $item->menuid = JText::sprintf(
                    'AMM_MIRRORING_MODULE',
                    '[<a href="' . JRoute::_('index.php?option=com_advancedmodules&task=module.edit&id=' . (int) $item->mirror_id) . '">'
                    . $item->mirror_id . '</a>]'
                );
                continue;
            }

            if ($item->mirror_id < 0)
            {
                $item->menuid = JText::sprintf(
                    'AMM_MIRRORING_MODULE_OPPOSITE',
                    '[<a href="' . JRoute::_('index.php?option=com_advancedmodules&task=module.edit&id=' . (int) ($item->mirror_id * -1)) . '">'
                    . ($item->mirror_id * -1) . '</a>]'
                );
                continue;
            }

            if (is_null($item->menuid))
            {
                $item->menuid = JText::_('JNONE');
                continue;
            }

            if ($item->menuid < 0)
            {
                $item->menuid = JText::_('COM_MODULES_ASSIGNED_VARIES_EXCEPT');
                continue;
            }

            if ($item->menuid > 0)
            {
                $item->menuid = JText::_('COM_MODULES_ASSIGNED_VARIES_ONLY');
                continue;
            }

            $item->menuid = JText::_('JALL');
        }
    }
}
