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
use Joomla\CMS\MVC\Controller\AdminController as JControllerAdmin;
use Joomla\CMS\Session\Session as JSession;
use Joomla\Utilities\ArrayHelper as JArrayHelper;

/**
 * Modules list controller class.
 */
class AdvancedModulesControllerModules extends JControllerAdmin
{
    /**
     * @var        string    The prefix to use with controller messages.
     */
    protected $text_prefix = 'COM_MODULES';

    /**
     * Method to clone an existing module.
     *
     * @return  void
     */
    public function duplicate()
    {
        // Check for request forgeries
        $this->checkToken();

        $pks = $this->input->post->get('cid', [], 'array');
        $pks = JArrayHelper::toInteger($pks);

        try
        {
            if (empty($pks))
            {
                throw new Exception(JText::_('COM_MODULES_ERROR_NO_MODULES_SELECTED'));
            }

            $model = $this->getModel();
            $model->duplicate($pks);
            $this->setMessage(JText::plural('COM_MODULES_N_MODULES_DUPLICATED', count($pks)));
        }
        catch (Exception $e)
        {
            throw new Exception($e->getMessage(), 500);
        }

        $this->setRedirect('index.php?option=com_advancedmodules&view=modules');
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param string $name   The model name. Optional.
     * @param string $prefix The class prefix. Optional.
     * @param array  $config Configuration array for model. Optional.
     *
     * @return  object  The model.
     */
    public function getModel($name = 'Module', $prefix = 'AdvancedModulesModel', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Method to set the color of items
     *
     * @return  void
     */
    public function setcolor()
    {
        // Check for request forgeries
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $pks = $this->input->post->get('cid', [], 'array');
        JArrayHelper::toInteger($pks);

        try
        {
            if (empty($pks))
            {
                throw new Exception(JText::_('COM_MODULES_ERROR_NO_MODULES_SELECTED'));
            }

            $color = $this->input->post->get('setcolor', '', 'string');
            $model = $this->getModel();
            $model->setcolor($pks, $color);
        }
        catch (Exception $e)
        {
            throw new Exception($e->getMessage(), 500);
        }

        $this->setRedirect('index.php?option=com_advancedmodules&view=modules');
    }
}
