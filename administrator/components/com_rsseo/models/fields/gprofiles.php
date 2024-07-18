<?php
/**
* @package RSSeo!
* @copyright (C) 2016 www.rsjoomla.com
* @license     GNU General Public License version 2 or later; see LICENSE
*/
defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

class JFormFieldGprofiles extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'Gprofiles';
	
	protected $analytics;
	
	public function __construct() {
		require_once JPATH_ADMINISTRATOR.'/components/com_rsseo/helpers/gapi.php';
		
		$config = rsseoHelper::getConfig();
		
		$options = array(
			"clientID"		=> trim($config->analytics_client_id),
			"clientSecret"	=> trim($config->analytics_secret),
			"scope"			=> "https://www.googleapis.com/auth/analytics",
			"redirect"  	=> JURI::root()."administrator/index.php?option=com_rsseo&task=analytics.connect",
			"sessionID"		=> 'rsseo.access_token'
		);
		
		$this->analytics = rsseoGoogleAPI::getInstance($options);
	}
	
	public function getInput() {
		if ($this->analytics->valid()) {
			JFactory::getDocument()->addScriptDeclaration("var rsseoWindow;");
			JFactory::getDocument()->addScriptDeclaration("function rsOpenWindow(url) { rsseoWindow = window.open(url, 'gconnect', 'width=800, height=600'); }");
			JFactory::getDocument()->addScriptDeclaration("function rsCloseWindow() { rsseoWindow.close(); }");
			
			return '<a href="'.$this->analytics->getAuthUrl().'" class="btn btn-success">'.JText::_('COM_RSSEO_AUTHENTIFICATE').'</a><br>'.JText::_('COM_RSSEO_CONFIGURATION_GOOGLE_ANALYTICS_PROFILES_INFO');
		}
		
		return parent::getInput();
	}
	
	protected function getOptions() {
		if (!$this->analytics->valid()) {
			try {
				return $this->analytics->getProfiles(false);
			} catch (Exception $e) {
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}
		}
	}
}