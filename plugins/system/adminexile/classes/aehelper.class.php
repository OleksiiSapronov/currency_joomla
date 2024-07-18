<?php

/**
 * @copyright	Copyright (C) 2010 Michael Richey. All rights reserved.
 * @license		GNU General Public License version 3; see LICENSE.txt
 */
defined('JPATH_BASE') or die;
if (!class_exists('AdminExileHelper'))
{

    class AdminExileHelper {

	public static function sendMail($recipient, $subject, $body) {
	    // prepare and send the email
	    $config = JFactory::getConfig();
	    $mailer = JFactory::getMailer();
	    $mailer->setSender(array($config->get('config.mailfrom'), $config->get('config.fromname')));
	    $mailer->addRecipient($recipient);
	    $mailer->setSubject($subject);
	    $mailer->setBody($body);
	    $mailer->SMTPDebug = 0;
	    $send = $mailer->Send();
	}

	public static function userIDFromUsername($username) {
	    $db = JFactory::getDbo();
	    $query = $db->getQuery(true);
	    $query->select('id')->from('#__users')->where('username=' . $db->quote($username));
	    $db->setQuery($query);
	    $userid = $db->loadResult();
	    return (int) $userid;
	}

	public static function stealth() {
	    // this is a stealth feature - prevent /administrator session cookie from being set
	    foreach (headers_list() as $header)
	    {
		if (preg_match('/Set-Cookie/', $header))
		{
		    header('Set-Cookie:');
		    break;
		}
	    }
	}

	public static function http_build_url($url, $parts = array(), $flags = HTTP_URL_REPLACE, &$new_url = false) {
	    $keys = array('user', 'pass', 'port', 'path', 'query', 'fragment');

	    // HTTP_URL_STRIP_ALL becomes all the HTTP_URL_STRIP_Xs
	    if ($flags & HTTP_URL_STRIP_ALL)
	    {
		$flags |= HTTP_URL_STRIP_USER;
		$flags |= HTTP_URL_STRIP_PASS;
		$flags |= HTTP_URL_STRIP_PORT;
		$flags |= HTTP_URL_STRIP_PATH;
		$flags |= HTTP_URL_STRIP_QUERY;
		$flags |= HTTP_URL_STRIP_FRAGMENT;
	    }
	    // HTTP_URL_STRIP_AUTH becomes HTTP_URL_STRIP_USER and HTTP_URL_STRIP_PASS
	    else if ($flags & HTTP_URL_STRIP_AUTH)
	    {
		$flags |= HTTP_URL_STRIP_USER;
		$flags |= HTTP_URL_STRIP_PASS;
	    }

	    // Parse the original URL
	    $parse_url = is_array($url) ? $url : parse_url($url);

	    // Scheme and Host are always replaced
	    if (isset($parts['scheme']))
		$parse_url['scheme'] = $parts['scheme'];
	    if (isset($parts['host']))
		$parse_url['host'] = $parts['host'];

	    // (If applicable) Replace the original URL with it's new parts
	    if ($flags & HTTP_URL_REPLACE)
	    {
		foreach ($keys as $key)
		{
		    if (isset($parts[$key]))
			$parse_url[$key] = $parts[$key];
		}
	    }
	    else
	    {
		// Join the original URL path with the new path
		if (isset($parts['path']) && ($flags & HTTP_URL_JOIN_PATH))
		{
		    if (isset($parse_url['path']))
			$parse_url['path'] = rtrim(str_replace(basename($parse_url['path']), '', $parse_url['path']), '/') . '/' . ltrim($parts['path'], '/');
		    else
			$parse_url['path'] = $parts['path'];
		}

		// Join the original query string with the new query string
		if (isset($parts['query']) && ($flags & HTTP_URL_JOIN_QUERY))
		{
		    if (isset($parse_url['query']))
			$parse_url['query'] .= '&' . $parts['query'];
		    else
			$parse_url['query'] = $parts['query'];
		}
	    }

	    // Strips all the applicable sections of the URL
	    // Note: Scheme and Host are never stripped
	    foreach ($keys as $key)
	    {
		if ($flags & (int) @constant('HTTP_URL_STRIP_' . strtoupper($key)))
		    unset($parse_url[$key]);
	    }

	    $new_url = $parse_url;

	    $ret = ((isset($parse_url['scheme'])) ? $parse_url['scheme'] . '://' : '')
		    . ((isset($parse_url['user'])) ? $parse_url['user'] . ((isset($parse_url['pass'])) ? ':' . $parse_url['pass'] : '') . '@' : '')
		    . ((isset($parse_url['host'])) ? $parse_url['host'] : '')
		    . ((isset($parse_url['port'])) ? ':' . $parse_url['port'] : '')
		    . ((isset($parse_url['path'])) ? $parse_url['path'] : '')
		    . ((isset($parse_url['query'])) ? '?' . $parse_url['query'] : '')
		    . ((isset($parse_url['fragment'])) ? '#' . $parse_url['fragment'] : '')
	    ;
	    return str_replace('//administrator', '/administrator', $ret);
	}

	public static function getIP() {
	    $ip = getenv('HTTP_CLIENT_IP') ? :
		    getenv('HTTP_X_FORWARDED_FOR') ? :
			    getenv('HTTP_X_FORWARDED') ? :
				    getenv('HTTP_FORWARDED_FOR') ? :
					    getenv('HTTP_FORWARDED') ? :
						    getenv('REMOTE_ADDR');
	    if (!filter_var($ip, FILTER_VALIDATE_IP) === false)
	    {
		$ip = getenv('REMOTE_ADDR');
	    }
	    return $ip;
	}

	public static function buildURL($query) {
	    $url = parse_url(JURI::root());
	    $url['path'] = explode('/', preg_replace(array('/(^\/)/', '/(\/$)/'), '', $url['path']));
	    $url['path'][] = 'administrator';
	    $url['path'] = '/' . implode('/', $url['path']);
	    $url['query'] = $query;
	    return AdminExileHelper::http_build_url($url);
	}

    }

}