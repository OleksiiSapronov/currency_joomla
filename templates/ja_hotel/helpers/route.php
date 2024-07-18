<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content Component Route Helper
 *
 * @static
 * @package     Joomla.Site
 * @subpackage  com_content
 * @since       1.5
 */
abstract class JATemplateHelperRoute
{
	protected static $lookup = array();

	protected static $lang_lookup = array();

    public static function getReservationAssetRoute($id, $roomTypeId = NULL, $language = 0)
    {
        $file = JPATH_SITE . '/components/com_solidres/helpers/route.php';
        $needles = array(
            'reservationasset'=> array((int) $id)

        );
        if(file_exists($file)) {
            require_once $file;
            return SolidresHelperRoute::getReservationAssetRoute($id, $roomTypeId, $language);
        }
        
        return true;
    }

    protected static function _findItem($needles = null)
    {
        $app		= JFactory::getApplication();
        $menus		= $app->getMenu('site');
        $language	= isset($needles['language']) ? $needles['language'] : '*';

        // Prepare the reverse lookup array.
        if (!isset(self::$lookup[$language]))
        {
            self::$lookup[$language] = array();

            $component	= JComponentHelper::getComponent('com_solidres');

            $attributes = array('component_id');
            $values = array($component->id);

            if ($language != '*')
            {
                $attributes[] = 'language';
                $values[] = array($needles['language'], '*');
            }

            $items		= $menus->getItems($attributes, $values);

            foreach ($items as $item)
            {
                if (isset($item->query) && isset($item->query['view']))
                {
                    $view = $item->query['view'];
                    if (!isset(self::$lookup[$language][$view]))
                    {
                        self::$lookup[$language][$view] = array();
                    }
                    if (isset($item->query['id'])) {

                        // here it will become a bit tricky
                        // language != * can override existing entries
                        // language == * cannot override existing entries
                        if (!isset(self::$lookup[$language][$view][$item->query['id']]) || $item->language != '*')
                        {
                            self::$lookup[$language][$view][$item->query['id']] = $item->id;
                        }
                    }
                }
            }
        }

        if ($needles)
        {
            foreach ($needles as $view => $ids)
            {
                if (isset(self::$lookup[$language][$view]))
                {
                    foreach ($ids as $id)
                    {
                        if (isset(self::$lookup[$language][$view][(int) $id]))
                        {
                            return self::$lookup[$language][$view][(int) $id];
                        }
                    }
                }
            }
        }

        // If not found, return the HUB search page
        $component	= JComponentHelper::getComponent('com_solidres');
        $attributes = array('component_id');
        $values = array($component->id);

        if ($language != '*')
        {
            $attributes[] = 'language';
            $values[] = array($needles['language'], '*');
        }

        $items	= $menus->getItems($attributes, $values);
        foreach ($items as $item)
        {
            if ($item->query['view'] == 'search')
            {
                return $item->id;
            }
        }

        // Check if the active menuitem matches the requested language
        $active = $menus->getActive();
        if ($active && $active->component == 'com_solidres' && ($language == '*' || in_array($active->language, array('*', $language)) || !JLanguageMultilang::isEnabled()))
        {
            return $active->id;
        }



        // If not found, return language specific home link
        $default = $menus->getDefault($language);
        return !empty($default->id) ? $default->id : null;
    }

}
