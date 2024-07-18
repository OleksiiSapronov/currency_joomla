<?php
/**
 * @package         Cache Cleaner
 * @version         7.2.2PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2020 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace RegularLabs\Plugin\System\CacheCleaner;

defined('_JEXEC') or die;

/**
 * Plugin that replaces stuff
 */
class Helper
{
	public function onAfterRoute()
	{
		Cache::clean();
	}
}
