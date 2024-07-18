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

namespace RegularLabs\Plugin\System\CacheCleaner\Cache;

defined('_JEXEC') or die;


use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use NetDNA as ApiNetDNA;
use RegularLabs\Plugin\System\CacheCleaner\Params;

class MaxCDN extends Cache
{
	public static function purge()
	{
		$input  = JFactory::getApplication()->input;
		$params = Params::get();

		$key   = $input->get('k', $params->maxcdn_authorization_key);
		$zones = $input->get('z', $params->maxcdn_zones);

		if (empty($key))
		{
			self::addError(JText::sprintf('CC_ERROR_CDN_NO_AUTHORIZATION_KEY', JText::_('CC_MAXCDN')));

			return -1;
		}

		if (empty($zones))
		{
			self::addError(JText::sprintf('CC_ERROR_CDN_NO_ZONES', JText::_('CC_MAXCDN')));

			return -1;
		}

		$api = self::getAPI($key);

		if ( ! $api || is_string($api))
		{
			self::addError(JText::sprintf('CC_ERROR_CDN_COULD_NOT_INITIATE_API', JText::_('CC_MAXCDN')));

			if (is_string($api))
			{
				self::addError($api);
			}

			return false;
		}

		$zones = explode(',', $zones);

		foreach ($zones as $zone)
		{
			$api_call = json_decode($api->delete('/zones/pull.json/' . $zone . '/cache'));

			if ( ! is_null($api_call) && isset($api_call->code) && ($api_call->code == 200 || $api_call->code == 201))
			{
				continue;
			}

			self::addError(JText::sprintf('CC_ERROR_CDN_COULD_NOT_PURGE_ZONE', JText::_('CC_MAXCDN'), $zone));

			return false;
		}

		return true;
	}

	public static function getAPI($key)
	{
		$keys = explode('+', $key, 3);

		if (count($keys) < 3)
		{
			return false;
		}

		list($alias, $consumer_key, $consumer_secret) = $keys;

		require_once __DIR__ . '/../Api/NetDNA.php';

		return new ApiNetDNA(trim($alias), trim($consumer_key), trim($consumer_secret));
	}
}
