<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Routing class from com_content
 *
 * @since  3.3
 */
class CurrenciesRouter extends JComponentRouterBase
{

	public function build(& $query)
	{
		JModelLegacy::addIncludePath(JPATH_ROOT . '/components/com_currencies/models');
		$segments      = array();
		$currencyModel = JModelLegacy::getInstance('currency', 'CurrenciesModel');
		$view          = isset($query['view']) ? $query['view'] : '';
		if (isset($query['view']))
		{
			$segments[] = $query['view'];
			unset($query['view']);
		};

		if (isset($query['base']))
		{
			$baseCurrency      = strtolower($currencyModel->getCurrencyName($query['base']));
			$baseCurrencyAlias = JFilterOutput::stringURLSafe($baseCurrency);

			if (isset($query['to']) && isset($query['value']))
			{
				$toCurrency      = strtolower($currencyModel->getCurrencyName($query['to']));
				$toCurrencyAlias = JFilterOutput::stringURLSafe($toCurrency);
				$segments[]      = floatval($query['value']) . '-' . $baseCurrencyAlias . '-' . strtolower($query['base']) . '-to-' . $toCurrencyAlias . '-' . strtolower($query['to']);
				unset($query['value']);
				unset($query['to']);
			}
			elseif (isset($query['to']) && !isset($query['value']))
			{
				//history page
				$toCurrency      = $currencyModel->getCurrencyName($query['to']);
				$toCurrencyAlias = JFilterOutput::stringURLSafe($toCurrency);
				$segments[]      = $baseCurrencyAlias . '-' . strtolower($query['base']) . '-to-' . $toCurrencyAlias . '-' . strtolower($query['to']);
				unset($query['to']);
			}
			elseif (!isset($query['to']) && isset($query['value']) && isset($query['layout']))
			{
				if ($query['layout'] == 'calculator')
				{
					$pageName = '-calculator';
				}
				else
				{
					$pageName = $query['layout'] == 'landing' ? '-page' : '';
				}

				$segments[] = $query['value'] . '-' . $baseCurrencyAlias . '-' . strtolower($query['base']) . $pageName;
				unset($query['layout']);
				unset($query['value']);
			}
			elseif (isset($query['layout']))
			{
				if ($query['layout'] == 'calculator')
				{
					$pageName = '-calculator';
				}
				else
				{
					$pageName = $query['layout'] == 'landing' ? '-page' : '';
				}

				$segments[] = $baseCurrencyAlias . '-' . strtolower($query['base']) . $pageName;
				unset($query['layout']);
			}
			else
			{
				$segments[] = $baseCurrencyAlias . '-' . strtolower($query['base']);
			}


			unset($query['base']);
		}

		if ($view == 'about')
		{
			$currency = $currencyModel->getCurrencyName($query['code']);
			$alias    = JFilterOutput::stringURLSafe($currency);

			$segments[] = $alias . '-' . $query['code'];
			unset($query['code']);
		}


		return $segments;
	}

	public function parse(&$segments)
	{
		$params = JComponentHelper::getParams('com_currencies');
		$vars   = array();
		// Count segments
		$count = count($segments);
		//Handle View and Identifier
		$currencies_codes = $this->getCurrenciesCodes();
		switch ($segments[0])
		{
			case 'pairs':
			case 'history':

				$vars['view'] = $segments[0];

				if (isset($segments[1]))
				{
					$urlParts = explode('-', $segments[1]);
					/*echo "<pre>";
					print_r($urlParts);
					exit;*/
					$currencyCodes = array();
					foreach ($urlParts as $part)
					{
						if (is_string($part) and ctype_upper($part)) {
							header( 'HTTP/1.1 301 Moved Permanently' );
							header( 'Location: ' . '/currencies/' . strtolower(join('/', $segments)) . '.html');
							exit();
						}
						if (in_array($part, $currencies_codes))
						{
							$currencyCodes[] = $part;
						}
					}
					$vars['base'] = $currencyCodes[0];
					if (isset($currencyCodes[1]))
					{
						$vars['to'] = $currencyCodes[1];
					}
					$firstElement = (float) array_shift($urlParts);
					$lastElement  = end($urlParts);

					if ($firstElement)
					{
						$vars['value'] = floatval($firstElement);
						if ($lastElement == 'calculator')
						{
							$vars['layout'] = 'calculator';
						}
					}
					else
					{

						if ($lastElement == 'calculator')
						{
							$vars['layout'] = 'calculator';
						}
						else
						{
							$vars['layout'] = $lastElement == 'page' ? 'landing' : '';
						}

					}
				}

				if (isset($segments[2]))
					$vars['Itemid'] = $segments[2];
				break;
			case 'about':
				$urlParts     = explode('-', $segments[1]);
				$vars['view'] = $segments[0];
				$vars['code'] = array_pop($urlParts);
				break;

		}

		return $vars;
	}

	/**
	 * Currencies router functions
	 *
	 * These functions are proxys for the new router interface
	 * for old SEF extensions.
	 *
	 * @param   array &$query An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @deprecated  4.0  Use Class based routers instead
	 */
	function currenciesBuildRoute(&$query)
	{
		$router = new CurrenciesRouter;

		return $router->build($query);
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * This function is a proxy for the new router interface
	 * for old SEF extensions.
	 *
	 * @param   array $segments The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since       3.3
	 * @deprecated  4.0  Use Class based routers instead
	 */
	function currenciesParseRoute($segments)
	{
		$router = new CurrenciesRouter;

		return $router->parse($segments);
	}


	function getCurrenciesCodes()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query
			->select('code')
			->from($db->quoteName('#__currencies_all_currencies'))
			->order($db->quoteName('code') . ' ASC');

		// Reset the query using our newly populated query object.
		$db->setQuery($query);

		// Load the results as a list of stdClass objects (see later for more options on retrieving data).
		$currncies_codes = [];
		foreach($db->loadObjectList() as $currency_obj) {
			$currencies_codes[] = strtolower($currency_obj->code);
		}
		return $currencies_codes;
	}
}