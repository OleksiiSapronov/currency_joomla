<?php
/**
 * @package         Regular Labs Extension Manager
 * @version         8.5.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\MVC\View\HtmlView as JView;
use RegularLabs\Library\ParametersNew as RL_Parameters;

jimport('joomla.application.component.view');

/**
 * View for the install processes
 */
class RegularLabsManagerViewProcess extends JView
{
    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        $action = JFactory::getApplication()->input->get('action');

        if ($action)
        {
            $model = $this->getModel();

            switch ($action)
            {
                case 'uninstall':
                    $model->uninstall(JFactory::getApplication()->input->get('id'));
                    break;

                case 'install':
                default:
                    $model->install(JFactory::getApplication()->input->get('id'), JFactory::getApplication()->input->getString('url'));
                    break;
            }

            parent::display('empty');

            return;
        }

        $this->items = $this->get('Items');
        $this->getConfig();

        parent::display($tpl);
    }

    /**
     * Function that gets the config settings
     */
    protected function getConfig()
    {
        if ( ! isset($this->config))
        {

            $this->config = RL_Parameters::getComponent('regularlabsmanager');
        }

        return $this->config;
    }
}
