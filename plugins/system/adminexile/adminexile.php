<?php

/**
 * @package plugin AdminExile
 * @copyright (C) 2010-2017 Michael Richey
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');
require_once(__DIR__ . '/classes/aehelper.class.php');

class plgSystemAdminExile extends JPlugin {

	private $_app;
	private $_ip;
	private $_key;
	
	private $_pass = false;
	private $_failed = false;
	private $_logged = false;

	public function __construct(&$subject, $config = array()) {
		$this->_app = JFactory::getApplication();
		$this->_ip = AdminExileHelper::getIP();
		
		if (
			($this->_app->isAdmin() && !JFactory::getUser()->guest) || // not logged into the backend
			$this->_app->isSite() // accessing frontend
		)
		{
			$this->_pass = true;
		}
		$this->_db = JFactory::getDbo();
		parent::__construct($subject, $config);
	}

	public function onAfterInitialise() {
		if ($this->_app->isSite() && \JFactory::getUser()->guest && $this->params->get('frontrestrict', 0))
		{
			return $this->_frontrestrict();
		}

		if (
			$this->_app->isSite() ||
			
			($this->_app->isAdmin() && !\JFactory::getUser()->guest))
		{
			return;
		}

		$this->_key = $this->params->get('key', 'adminexile');

		if ($this->params->get('maillink', 1))
		{
			$this->_maillink();
		}

		

		if ($this->_pass || $this->_app->getUserState("plg_sys_adminexile.$this->_key", false) || $this->_keyauth())
		{
			$this->_authorize();
		}
		else
		{
			$this->_fail();
		}
	}

	

	private function _keyauth() {
		$keyvalue = filter_input(INPUT_GET, $this->_key);
		$key = !\is_null($keyvalue);
		return $this->params->get('twofactor', false) ? ($key && $this->params->get('keyvalue', false) === $keyvalue) : $key;
	}

	private function _authorize() {
		$this->_app->setUserState("plg_sys_adminexile.$this->_key", true);
	}

	private function _fail($action = true) {

		$user = JFactory::getUser();
		if ($user->id)
		{
			JPluginHelper::importPlugin('user');
			$dispatcher = JEventDispatcher::getInstance();
			$dispatcher->trigger('onUserLogout', array(array('id', $user->id)));
		}

		AdminExileHelper::stealth();
		$this->_faillog('(' . $this->_ip . ') failed to authenticate via AdminExile');
		if ($action)
		{
			$this->_failAction();
		}
		$this->_failed = true;
		switch ($this->params->get('redirect', 'HOME'))
		{
			case '{HOME}': // 2.x fallback
			case 'HOME':
				header("Location: " . JURI::root());
				break;
			case '{404}': // 2.x fallback
			case '404':
				header(\filter_input(INPUT_SERVER, 'SERVER_PROTOCOL') . ' 404 Not Found');
				header("Status: 404 Not Found");
				$find = array('{url}', '{serversignature}');
				$replace = array(\filter_input(INPUT_SERVER, 'REQUEST_URI'), \filter_input(INPUT_SERVER, 'SERVER_SIGNATURE'));
				die(str_replace($find, $replace, $this->params->get('fourofour')));
				break;
			default:
				$destination = $this->params->get('redirecturl', 'https://www.fbi.gov');
				if ($destination == 'https://www.richeyweb.com')
				{
					$destination = 'https://www.fbi.gov';
				}
				header("Location: " . $destination);
				break;
		}
	}

	public function _frontrestrict() {
		$username = $this->_app->input->post->get('username', false);
		if (!$username)
		{
			return;
		}
		$userid = AdminExileHelper::userIDFromUsername($username);
		if (!is_numeric($userid) || $userid === 0)
		{
			return;
		}
		$user = JFactory::getUser($userid);
		if (!count(array_intersect($user->getAuthorisedGroups(), $this->params->get('restrictgroup', array()))))
		{
			return;
		}
		$this->_faillog('AdminExile prevented user (' . $username . ') attempt to log into the frontend via ' . $this->_ip);
		JFactory::getSession()->close();
		return;
	}

	private function _maillink() {
		$username = filter_input(INPUT_GET, 'maillink');
		if ($username === null)
		{
			return;
		}
		$this->loadLanguage('plg_system_adminexile');
		$userid = AdminExileHelper::userIDFromUsername($username);
		if (is_numeric($userid) && $userid != 0)
		{
			$user = JFactory::getUser($userid);
			if (!count(array_intersect($user->getAuthorisedGroups(), $this->params->get('maillinkgroup', array()))))
			{
				$this->_fail();
			}
			$query = $this->params->get('twofactor', false) ? http_build_query(array($this->_key => urlencode($this->params->get('keyvalue', false)))) : $this->_key;
			$secureurl = AdminExileHelper::buildURL($query);
			$subject = JText::sprintf('PLG_SYS_ADMINEXILE_EMAIL_SUBJECT', $this->_app->getCfg('sitename'));
			$body = JText::sprintf('PLG_SYS_ADMINEXILE_EMAIL_BODY', $secureurl, $user->email, $user->username, $this->_ip);
			AdminExileHelper::sendMail($user->email, $subject, $body);
		}
		$this->_fail(false);
	}

	private function _faillog($log) {
		if ($this->params->get('faillog', 0) && !$this->_logged)
		{
			$this->_logged = true;
			error_log($log);
		}
	}

	private function _failAction() {
		
		return;
	}

	
}
