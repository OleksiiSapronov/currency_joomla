<?php
/**
 * @package         Sourcerer
 * @version         8.4.2PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2020 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/script.install.helper.php';

class PlgSystemSourcererInstallerScript extends PlgSystemSourcererInstallerScriptHelper
{
	public $name           = 'SOURCERER';
	public $alias          = 'sourcerer';
	public $extension_type = 'plugin';

	public function uninstall($adapter)
	{
		$this->uninstallPlugin($this->extname, 'editors-xtd');
	}
}
