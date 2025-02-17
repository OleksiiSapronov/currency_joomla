<?php
/**
 * Plugin Helper File: Variables
 *
 * @package         ReReplacer
 * @version         5.13.5
 *
 * @author          Peter van Westen <peter@nonumber.nl>
 * @link            http://www.nonumber.nl
 * @copyright       Copyright © 2015 NoNumber All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once JPATH_PLUGINS . '/system/nnframework/helpers/text.php';

class plgSystemReReplacerHelperVariables
{
	var $helpers = array();
	var $user = null;
	var $contact = null;
	var $profile = null;

	public function __construct()
	{
		require_once __DIR__ . '/helpers.php';
		$this->helpers = plgSystemReReplacerHelpers::getInstance();
	}

	public function protectVariables(&$string)
	{
		$string = preg_replace('#(\[\(/?)(user:|date:|random:|escape|lowercase|uppercase)#', '\1x\2', $string);
	}

	public function unprotectVariables(&$string)
	{
		$string = preg_replace('#(\[\(/?)x(user:|date:|random:|escape|lowercase|uppercase)#', '\1\2', $string);
	}

	public function replaceVariables(&$string)
	{
		$this->replaceVariableTagByType($string, 'user');
		$this->replaceVariableTagByType($string, 'date');
		$this->replaceVariableTagByType($string, 'random');
		$this->replaceVariableDoubleTagByType($string, 'escape');
		$this->replaceVariableDoubleTagByType($string, 'lowercase');
		$this->replaceVariableDoubleTagByType($string, 'uppercase');

		$this->unprotectVariables($string);
	}

	// single [[tag:...]] style tag on single line
	private function replaceVariableTagByType(&$string, $type)
	{
		if (strpos($string, '[[' . $type . ':') === false)
		{
			return;
		}

		if (preg_match_all('#\[\[' . $type . '\:(.*?)\]\]#', $string, $matches, PREG_SET_ORDER) < 1)
		{
			return;
		}

		foreach ($matches as $match)
		{
			$this->{'replaceVariableMatch' . ucfirst($type)}($string, $match);
		}
	}

	// double [[tag]]...[[/tag]] style tag on multiple lines
	private function replaceVariableDoubleTagByType(&$string, $type)
	{
		if (strpos($string, '[[' . $type . ']]') === false)
		{
			return;
		}

		if (preg_match_all('#\[\[' . $type . '\]\](.*?)\[\[/' . $type . '\]\]#s', $string, $matches, PREG_SET_ORDER) < 1)
		{
			return;
		}

		foreach ($matches as $match)
		{
			$this->{'replaceVariableMatch' . ucfirst($type)}($string, $match);
		}
	}

	private function replaceVariableMatchUser(&$string, $match)
	{
		if ($match['1'] == 'password')
		{
			$string = str_replace($match['0'], '', $string);

			return;
		}

		$this->initParamsUser();

		if ($this->user->guest)
		{
			$string = str_replace($match['0'], '', $string);

			return;
		}

		if (isset($this->user->{$match['1']}))
		{
			$string = str_replace($match['0'], $this->user->{$match['1']}, $string);

			return;
		}

		$this->initParamsContact();

		if (isset($this->contact->{$match['1']}))
		{
			$string = str_replace($match['0'], $this->contact->{$match['1']}, $string);

			return;
		}

		$this->initParamsProfile();

		if (isset($this->profile->{$match['1']}))
		{
			$string = str_replace($match['0'], $this->profile->{$match['1']}, $string);

			return;
		}

		$string = str_replace($match['0'], '', $string);
	}

	private function replaceVariableMatchDate(&$string, $match)
	{
		$string = str_replace($match['0'], $this->getDateFromFormat($match['1']), $string);
	}

	private function replaceVariableMatchRandom(&$string, $match)
	{
		$range = trim($match['1']);

		if (!preg_match('#^([0-9]+)-([0-9]+)$#', $range, $range))
		{
			return;
		}

		$replace = rand((int) $range['1'], (int) $range['2']);
		$string = nnText::strReplaceOnce($match['0'], $replace, $string);
	}

	private function replaceVariableMatchEscape(&$string, $match)
	{
		$string = str_replace($match['0'], addslashes($match['1']), $string);
	}

	private function replaceVariableMatchLowercase(&$string, $match)
	{
		$string = str_replace($match['0'], strtolower($match['1']), $string);
	}

	private function replaceVariableMatchUppercase(&$string, $match)
	{
		$string = str_replace($match['0'], strtoupper($match['1']), $string);
	}

	private function getDateFromFormat($date)
	{
		if ($date && strpos($date, '%') !== false)
		{
			$date = nnText::dateToDateFormat($date);
		}

		$date = str_replace('[TH]', '[--==--]', $date);

		$date = JHtml::_('date', 'now', $date);

		$this->replaceThIndDate($date, '[--==--]');

		return $date;
	}

	private function replaceThIndDate(&$date, $th = '[TH]')
	{
		if (strpos($date, $th) === false)
		{
			return;
		}

		if (preg_match_all('#([0-9]+)' . preg_quote($th, '#') . '#si', $date, $date_matches, PREG_SET_ORDER) < 1)
		{
			$date = str_replace($th, 'th', $date);

			return;
		}

		foreach ($date_matches as $date_match)
		{
			$suffix = 'th';
			switch ($date_match['1'])
			{
				case 1:
				case 21:
				case 31:
					$suffix = 'st';
					break;
				case 2:
				case 22:
				case 32:
					$suffix = 'rd';
					break;
				case 3:
				case 23:
					$suffix = 'rd';
					break;
			}
			$date = nnText::strReplaceOnce($date_match['0'], $date_match['1'] . $suffix, $date);
		}

		$date = str_replace($th, 'th', $date);
	}

	private function initParamsUser()
	{
		if ($this->user)
		{
			return;
		}

		$this->user = JFactory::getUser();
		self::flattenParams($this->user);
	}

	private function initParamsContact()
	{
		if ($this->contact)
		{
			return;
		}

		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
			->select('c.*')
			->from('#__' . $this->helpers->getParams()->contact_table . ' as c')
			->where('c.user_id = ' . (int) $this->user->id);
		$db->setQuery($query);
		$this->contact = $db->loadObject();

		if (!$this->contact)
		{
			$this->contact = new stdClass();
			$this->contact->x = '';

			return;
		}

		self::flattenParams($this->contact);
	}

	private function initParamsProfile()
	{
		if ($this->profile)
		{
			return;
		}

		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
			->select('p.profile_key, p.profile_value')
			->from('#__user_profiles as p')
			->where('p.user_id = ' . (int) $this->user->id);
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$profile = new stdClass();
		$profile->x = '';
		foreach ($rows as $row)
		{
			$profile->{substr($row->profile_key, 8)} = json_decode($row->profile_value);
		}

		$this->profile = $profile;
	}

	private function flattenParams(&$object)
	{
		foreach ($object as $propertie)
		{
			if (!$propertie || !is_string($propertie) || $propertie['0'] != '{')
			{
				continue;
			}

			$propertie = json_decode($propertie);

			foreach ($propertie as $key => $val)
			{
				if (!$val || isset($object->{$key}))
				{
					continue;
				}

				$object->{$key} = $val;
			}
		}
	}
}

