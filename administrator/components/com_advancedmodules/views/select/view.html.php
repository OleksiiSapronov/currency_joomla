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

use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Layout\FileLayout as JLayoutFile;
use Joomla\CMS\MVC\View\HtmlView as JView;
use Joomla\CMS\Toolbar\Toolbar as JToolbar;

/**
 * HTML View class for the Modules component
 */
class AdvancedModulesViewSelect extends JView
{
    protected $items;
    protected $state;

    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        $state = $this->get('State');
        $items = $this->get('Items');

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            throw new Exception(implode("\n", $errors), 500);
        }

        $this->state = &$state;
        $this->items = &$items;

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     */
    protected function addToolbar()
    {
        // Add page title
        JToolbarHelper::title(JText::_('COM_MODULES_MANAGER_MODULES'), 'advancedmodulemanager icon-reglab');

        // Get the toolbar object instance
        $bar = JToolbar::getInstance('toolbar');

        // Instantiate a new JLayoutFile instance and render the layout
        $layout = new JLayoutFile('toolbar.cancelselect');

        $bar->appendButton('Custom', $layout->render([]), 'new');
    }
}
