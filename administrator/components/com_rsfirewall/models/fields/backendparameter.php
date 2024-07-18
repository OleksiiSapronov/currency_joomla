<?php
/*
 * @package 	RSFirewall!
 * @copyright 	(c) 2009 - 2024 RSJoomla!
 * @link 		https://www.rsjoomla.com/joomla-extensions/joomla-security.html
 * @license 	GNU General Public License https://www.gnu.org/licenses/gpl-3.0.en.html
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Form\Field\TextField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

FormHelper::loadFieldClass('text');

if (version_compare(JVERSION, '4.0', '<'))
{
	JLoader::registerAlias('Joomla\\CMS\\Form\\Field\\TextField', 'JFormFieldText');
}

class JFormFieldBackendParameter extends TextField
{
	protected function getInput()
	{
		$parameter = RSFirewallConfig::getInstance()->get('backend_password_parameter');

		if (strlen($parameter))
		{
			$parameter .= '=';
		}

		return parent::getInput() . '<br /><p><small>' . Text::sprintf('COM_RSFIREWALL_BACKEND_PASSWORD_EXAMPLE', $this->escape(Uri::root() . 'administrator/?') . '<span id="backend_password_placeholder">' . $this->escape($parameter) . '</span>') . '</small></p>';
	}

	protected function escape($string)
	{
		return htmlentities($string, ENT_COMPAT, 'utf-8');
	}
}