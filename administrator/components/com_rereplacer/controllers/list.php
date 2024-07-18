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
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\MVC\Controller\AdminController as JControllerAdmin;
use Joomla\CMS\Session\Session as JSession;

jimport('joomla.application.component.controlleradmin');

/**
 * List Controller
 */
class ReReplacerControllerList extends JControllerAdmin
{
    /**
     * @var        string    The prefix to use with controller messages.
     */
    protected $text_prefix = 'RL';

    /**
     * Method to update a record.
     */
    public function activate()
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        // Initialise variables.
        $ids     = JFactory::getApplication()->input->get('cid', [], 'array');
        $name    = JFactory::getApplication()->input->getString('name');
        $search  = JFactory::getApplication()->input->getString('search');
        $replace = JFactory::getApplication()->input->getString('replace');

        if (empty($ids))
        {
            throw new Exception(JText::_('COM_REDIRECT_NO_ITEM_SELECTED'), 500);
        }

        // Get the model.
        $model = $this->getModel();

        JArrayHelper::toInteger($ids);

        // Remove the list.
        if ( ! $model->activate($ids, $name, $search, $replace))
        {
            throw new Exception($model->getError(), 500);
        }

        $this->setMessage(JText::plural('RL_N_ITEMS_UPDATED', count($ids)));

        $this->setRedirect('index.php?option=com_rereplacer&view=list');
    }

    /**
     * Copy Method
     * Copy all items specified by array cid
     * and set Redirection to the list of items
     */
    public function copy()
    {
        $ids = JFactory::getApplication()->input->get('cid', [], 'array');

        // Get the model.
        $model      = $this->getModel('List');
        $model_item = $this->getModel('Item');

        $model->copy($ids, $model_item);
    }

    /**
     * Export Method
     * Export the selected items specified by id
     */
    public function export()
    {
        $ids = JFactory::getApplication()->input->get('cid', [], 'array');

        // Get the model.
        $model = $this->getModel('List');

        $model->export($ids);
    }

    /**
     * Proxy for getModel.
     */
    public function getModel($name = 'Item', $prefix = 'ReReplacerModel', $config = ['ignore_request' => true])
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    /**
     * Import Method
     * Set layout to import
     */
    public function import()
    {
        $file = JRequest::getVar('file', '', 'files');

        if (empty($file))
        {
            $this->setRedirect('index.php?option=com_rereplacer&view=list&layout=import');

            return;
        }

        if ( ! isset($file['name']))
        {
            $msg = JText::_('RR_PLEASE_CHOOSE_A_VALID_FILE');
            $this->setRedirect('index.php?option=com_rereplacer&view=list&layout=import', $msg);
        }

        // Get the model.
        $model      = $this->getModel('List');
        $model_item = $this->getModel('Item');
        $model->import($model_item);
    }

    /**
     * Method to save the submitted ordering values for records via AJAX.
     *
     * @return    void
     *
     * @since   3.0
     */
    public function saveOrderAjax()
    {
        $pks   = $this->input->post->get('cid', [], 'array');
        $order = $this->input->post->get('order', [], 'array');

        // Sanitize the input
        JArrayHelper::toInteger($pks);
        JArrayHelper::toInteger($order);

        // Get the model
        $model = $this->getModel();

        // Save the ordering
        $return = $model->saveorder($pks, $order);

        if ($return)
        {
            echo "1";
        }

        // Close the application
        JFactory::getApplication()->close();
    }
}
