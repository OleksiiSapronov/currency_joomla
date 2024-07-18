<?php
/**
 * @package         Cache Cleaner
 * @version         7.3.3PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2020 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/script.install.helper.php';

class Mod_CacheCleanerInstallerScript extends Mod_CacheCleanerInstallerScriptHelper
{
	public $name            = 'CACHECLEANER';
	public $alias           = 'cachecleaner';
	public $extension_type  = 'module';
	public $module_position = 'status';
	public $client_id       = 1;

	public function uninstall($adapter)
	{
		$this->uninstallPlugin($this->extname, 'system');
	}
}
