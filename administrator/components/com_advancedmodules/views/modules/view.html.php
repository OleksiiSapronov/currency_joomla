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

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Helper\ContentHelper as JContentHelper;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Layout\FileLayout as JLayoutFile;
use Joomla\CMS\MVC\View\HtmlView as JView;
use Joomla\CMS\Toolbar\Toolbar as JToolbar;
use RegularLabs\Library\ParametersNew as RL_Parameters;

/**
 * View class for a list of modules.
 */
class AdvancedModulesViewModules extends JView
{
    protected $items;

    protected $pagination;

    protected $state;

    /**
     * Display the view
     *
     * @param string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        $this->getConfig();

        foreach ($this->items as $i => $item)
        {
            $this->items[$i]->params = json_decode($item->advancedparams);

            if (is_null($this->items[$i]->params))
            {
                $this->items[$i]->params = (object) [];
            }
        }

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            throw new Exception(implode("\n", $errors), 500);
        }

        if (JFactory::getApplication()->input->get('layout') != 'modal')
        {
            $this->addToolbar();
        }

        // Include the component HTML helpers.
        JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     */
    protected function addToolbar()
    {

        $state = $this->get('State');
        $canDo = JContentHelper::getActions('com_modules');
        $user  = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

        // Get the toolbar object instance
        $bar = JToolbar::getInstance('toolbar');

        if ($this->config->list_title)
        {
            JToolbarHelper::title(
                JText::_(
                    $state->get('filter.client_id') ? 'COM_MODULES_MANAGER_MODULES_ADMIN' : 'COM_MODULES_MANAGER_MODULES_SITE'
                ),
                'cube module'
            );
        }
        else
        {
            JToolbarHelper::title(
                JText::_('ADVANCEDMODULEMANAGER'),
                'advancedmodulemanager icon-reglab'
            );
        }

        if ($canDo->get('core.create'))
        {
            // Instantiate a new JLayoutFile instance and render the layout
            $layout = new JLayoutFile('toolbar.newmodule');

            $bar->appendButton('Custom', $layout->render([]), 'new');
        }

        if ($canDo->get('core.edit'))
        {
            JToolbarHelper::editList('module.edit');
        }

        if ($canDo->get('core.create'))
        {
            JToolbarHelper::custom('modules.duplicate', 'copy', 'copy_f2', 'JTOOLBAR_DUPLICATE', true);
        }

        if ($canDo->get('core.edit.state'))
        {
            JToolbarHelper::publish('modules.publish', 'JTOOLBAR_PUBLISH', true);
            JToolbarHelper::unpublish('modules.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            JToolbarHelper::checkin('modules.checkin');
        }

        if ($state->get('filter.state') == -2 && $canDo->get('core.delete'))
        {
            JToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', 'modules.delete', 'JTOOLBAR_EMPTY_TRASH');
        }
        elseif ($canDo->get('core.edit.state'))
        {
            JToolbarHelper::trash('modules.trash');
        }

        // Add a batch button
        if (
            $user->authorise('core.create', 'com_modules')
            && $user->authorise('core.edit', 'com_modules')
            && $user->authorise('core.edit.state', 'com_modules')
        )
        {
            JHtml::_('bootstrap.modal', 'collapseModal');
            $title = JText::_('JTOOLBAR_BATCH');

            // Instantiate a new JLayoutFile instance and render the batch button
            $layout = new JLayoutFile('joomla.toolbar.batch');

            $dhtml = $layout->render(['title' => $title]);
            $bar->appendButton('Custom', $dhtml, 'batch');
        }

        if ($canDo->get('core.admin'))
        {
            JToolbarHelper::preferences('com_advancedmodules', 600, 900);
        }

        JToolbarHelper::help('JHELP_EXTENSIONS_MODULE_MANAGER');
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
     * Returns an array of fields the table can be sorted by
     *
     * @return  array  Array containing the field name to sort by as the key and display text as value
     */
    protected function getSortFields()
    {
        $fields = [
            'ordering'       => JText::_('JGRID_HEADING_ORDERING'),
            'a.published'    => JText::_('JSTATUS'),
            'color'          => JText::_('RL_COLOR'),
            'a.title'        => JText::_('JGLOBAL_TITLE'),
            'position'       => JText::_('COM_MODULES_HEADING_POSITION'),
            'name'           => JText::_('COM_MODULES_HEADING_MODULE'),
            'menuid'         => JText::_('RL_MENU_ITEMS'),
            'a.access'       => JText::_('JGRID_HEADING_ACCESS'),
            'language_title' => JText::_('JGRID_HEADING_LANGUAGE'),
            'a.id'           => JText::_('JGRID_HEADING_ID'),
        ];

        if ($this->getLayout() != 'default')
        {
            unset($fields['ordering']);
            unset($fields['a.published']);
            unset($fields['color']);
        }

        if ($this->state->get('filter.client_id') != 'site')
        {
            unset($fields['menuid']);
        }

        return $fields;
    }
}
