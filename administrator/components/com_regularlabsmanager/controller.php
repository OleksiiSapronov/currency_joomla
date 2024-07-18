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

use Joomla\CMS\MVC\Controller\BaseController as JController;

/**
 * Master Display Controller
 */
class RegularLabsManagerController extends JController
{
    /**
     * @var        string    The default view.
     */
    protected $default_view = 'default';
}
