<?php
/**
* @package RSSeo!
* @copyright (C) 2016 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Controller\FormController;

class rsseoControllerRedirect extends FormController
{
	/**
	 * Class constructor.
	 *
	 * @param   array  $config  A named array of configuration variables.
	 *
	 * @since	1.6
	 */
	public function __construct() {
		parent::__construct();
	}
	
	public function savemultiple() {
		$model = parent::getModel('Redirect', 'rsseoModel', array('ignore_request' => true));
		
		$model->savemultiple();
		
		return $this->setRedirect('index.php?option=com_rsseo&view=redirects');
	}
}