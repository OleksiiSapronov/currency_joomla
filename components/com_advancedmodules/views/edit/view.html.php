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

use Joomla\CMS\Form\Form as JForm;

require_once JPATH_ADMINISTRATOR . '/components/com_advancedmodules/views/module/view.html.php';
require_once JPATH_ADMINISTRATOR . '/components/com_advancedmodules/helpers/modules.php';

class AdvancedModulesViewEdit extends AdvancedModulesViewModule
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
        $this->setLayout('edit');

        parent::display($tpl);
    }

    /**
     * Method to get the record form.
     *
     * @param array   $data     Data for the form.
     * @param boolean $loadData True if the form is to load its own data (default case), false if not.
     *
     * @return  JForm  A JForm object on success, false on failure
     */
    public function getForm($data = [], $loadData = true)
    {
        return $this->getModel()->getForm($data, $loadData);
    }

    /**
     * Function that gets the config settings
     *
     * @return    Object
     */
    protected function addToolbar()
    {
        return;
    }

}
