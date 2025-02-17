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
use Joomla\CMS\MVC\View\HtmlView as JView;

/**
 * View to edit a module.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 * @since       3.2
 */
class AdvancedModulesViewModule extends JView
{
    protected $form;
    protected $item;
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
        $app = JFactory::getApplication();

        try
        {
            $this->item = $this->get('Item');
        }
        catch (Exception $e)
        {
            $app->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        $paramsList = $this->item->getProperties();

        unset($paramsList['xml']);

        $paramsList = json_encode($paramsList);

        return $paramsList;
    }
}
