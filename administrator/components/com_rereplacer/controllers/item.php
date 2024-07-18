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

use Joomla\CMS\MVC\Controller\FormController as JControllerForm;

jimport('joomla.application.component.controllerform');

/**
 * Item Controller
 */
class ReReplacerControllerItem extends JControllerForm
{
    /**
     * @var        string    The prefix to use with controller messages.
     */
    protected $text_prefix = 'RL';
    // Parent class access checks are sufficient for this controller.
}
