<?php

class CurrenciesHelper
{
	static function doRequest($url, $message = '')
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$content = curl_exec($ch);
		curl_close($ch);
		print_r($content);
	}

	/**
	 * function xml2array
	 *
	 * This function is part of the PHP manual.
	 *
	 * The PHP manual text and comments are covered by the Creative Commons
	 * Attribution 3.0 License, copyright (c) the PHP Documentation Group
	 *
	 * @author  k dot antczak at livedata dot pl
	 * @date    2011-04-22 06:08 UTC
	 * @link    http://www.php.net/manual/en/ref.simplexml.php#103617
	 * @license http://www.php.net/license/index.php#doc-lic
	 * @license http://creativecommons.org/licenses/by/3.0/
	 * @license CC-BY-3.0 <http://spdx.org/licenses/CC-BY-3.0>
	 */
	static function xml2array($xmlObject, $out = array())
	{
		foreach ((array) $xmlObject as $index => $node)
			$out[$index] = (is_object($node)) ? self::xml2array($node) : $node;

		return $out;
	}

	static function preBuildLink($view, $code, $name, $to = null, $toName = null, $value = null, $layout = null, $Itemid = null)
	{
		$link = sprintf('index.php?option=com_currencies&view=%s&base=%s&name=%s%s&Itemid='.$Itemid,
			$view, $code, $name, $layout ? '&layout='.$layout : '', $to ? '&to='.$to : '', $toName ? '&toName='.$toName : '', $value ? '&value='.$value : '', $Itemid ? '&Itemid='.$Itemid : '');
		return $link;
	}
}