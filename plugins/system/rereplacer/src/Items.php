<?php
/**
 * @package         ReReplacer
 * @version         13.2.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

namespace RegularLabs\Plugin\System\ReReplacer;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\ParametersNew as RL_Parameters;
use RegularLabs\Library\Protect as RL_Protect;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\Xml as RL_Xml;

class Items
{
    static $items = [];

    public static function filterItemList(&$items, $article = 0)
    {
        foreach ($items as $key => &$item)
        {
            if (
                (RL_Document::isClient('administrator') && $item->enable_in_admin == 0)
                || (RL_Document::isClient('site') && $item->enable_in_admin == 2)
            )
            {
                unset($items[$key]);
                continue;
            }

            $item = Conditions::itemPass($item, $article);

            if ( ! $item)
            {
                unset($items[$key]);
            }
        }
    }

    public static function getItemList($area = 'article')
    {
        if (isset(self::$items[$area]))
        {
            return self::$items[$area];
        }

        $db    = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('r.*')
            ->from('#__rereplacer AS r')
            ->where('r.published = 1');
        $where = 'r.area = ' . $db->quote($area);
        $where .= ' OR r.params LIKE ' . $db->quote('%"use_xml":"1"%');
        $query->where('(' . $where . ')')
            ->order('r.ordering, r.id');
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $items = [];

        if (empty($rows))
        {
            self::$items[$area] = $items;

            return self::$items[$area];
        }

        foreach ($rows as $row)
        {
            if ( ! $item = self::getItem($row, $area))
            {
                continue;
            }

            if (is_array($item))
            {
                $items = array_merge($items, $item);
                continue;
            }

            $items[] = $item;
        }

        if ($area != 'articles')
        {
            self::filterItemList($items);
        }

        self::$items[$area] = $items;

        return self::$items[$area];
    }

    public static function getItemsFromXml($xml_data, $item, $area)
    {
        // prevent html tags in strings to mess up xml structure
        $xml_data = str_replace(
            ['<search>', '<replace>', '</search>', '</replace>'],
            ['<search><![CDATA[', '<replace><![CDATA[', ']]></search>', ']]></replace>'],
            $xml_data
        );

        if (strpos($xml_data, '<param name="other_replace">') !== false)
        {
            $xml_data = RL_RegEx::replace('(<param name="other_replace">)(.*?)(</param>)', '\1<![CDATA[\2]]>\3', $xml_data);
        }

        $xml_data = str_replace(
            ['<![CDATA[<![CDATA[', ']]>]]></'],
            ['<![CDATA[', ']]></'],
            $xml_data
        );

        $xml = RL_Xml::toObject($xml_data, 'items');

        if ( ! isset($xml->item))
        {
            return false;
        }

        if ( ! is_array($xml->item))
        {
            $xml->item = [$xml->item];
        }

        $items = [];

        foreach ($xml->item as $data)
        {
            $subitem = self::getItemFromXmlData($item, $data, $area);

            if (empty($subitem))
            {
                continue;
            }

            if (isset($subitem->published) && ! $subitem->published)
            {
                continue;
            }

            $items[] = $subitem;
        }

        return $items;
    }

    private static function getItem($row, $area = 'article')
    {
//        if ( ! ((substr($row->params, 0, 1) != '{')
//            && (substr($row->params, -1, 1) != '}')))
//        {
//            $row->params = RL_String::html_entity_decoder($row->params);
//        }

        $item = RL_Parameters::getObjectFromRegistry($row->params, JPATH_ADMINISTRATOR . '/components/com_rereplacer/item_params.xml');

        unset($row->params);

        foreach ($row as $key => $param)
        {
            $item->{$key} = $param;
        }

        if ($item->use_xml)
        {
            return self::getItemsFromItemXml($item, $area);
        }

        if ( ! self::itemPassChecks($item, $area))
        {
            return false;
        }

        if (strlen($item->search) < 1)
        {
            return false;
        }

        self::prepareString($item->search);
        self::prepareReplaceString($item->replace);

        return $item;
    }

    private static function getItemFromXmlData($item, $xml_data, $area)
    {
        if ( ! isset($xml_data->search))
        {
            return false;
        }

        $item = clone $item;

        $item->search  = $xml_data->search;
        $item->replace = $xml_data->replace ?? '';

        self::prepareString($item->search);
        self::prepareReplaceString($item->replace);

        $xml_data->param ??= [];

        if (isset($xml_data->params->param))
        {
            $xml_data->param = $xml_data->params->param;
            unset($xml_data->params);
        }

        if ( ! is_array($xml_data->param))
        {
            $xml_data->param = [$xml_data->param];
        }

        foreach ($xml_data->param as $param)
        {
            if (isset($param->{"@attributes"}) && isset($param->{"@attributes"}->name) && isset($param->{"@attributes"}->value))
            {
                $param = $param->{"@attributes"};
            }

            if ( ! isset($param->name) || ! isset($param->value))
            {
                continue;
            }

            $item->{$param->name} = $param->value;
        }

        if ( ! self::itemPassChecks($item, $area))
        {
            return false;
        }

        return $item;
    }

    private static function getItemsFromItemXml($item, $area)
    {
        if (empty($item->xml))
        {
            return false;
        }

        jimport('joomla.filesystem.file');

        $file = str_replace('//', '/', JPATH_SITE . '/' . str_replace('\\', '/', $item->xml));

        if ( ! file_exists($file))
        {
            return false;
        }

        $xml_data = file_get_contents($file);

        return self::getItemsFromXml($xml_data, $item, $area);
    }

    private static function itemPassChecks($item, $area)
    {
        if ($item->area != $area)
        {
            return false;
        }

        if (empty($item->search))
        {
            return false;
        }

        if (
            (RL_Document::isFeed() && ! $item->enable_in_feeds)
            || ( ! RL_Document::isFeed() && $item->enable_in_feeds == 2)
        )
        {
            return false;
        }

        return true;
    }

    private static function prepareReplaceString(&$string)
    {
        [$tag, $characters] = RL_Protect::getSourcererTag();

        if (empty($tag))
        {
            return;
        }

        [$start, $end] = explode('.', $characters);

        self::prepareString($string);

        if (strpos($string, $start . '/' . $tag . $end) === false)
        {
            return;
        }

        // fix usage of non-protected {source} tags to {source 0}
        $string = str_replace($start . $tag . $end, $start . $tag . ' 0' . $end, $string);
    }

    private static function prepareString(&$string)
    {
        if (is_string($string))
        {
            return;
        }

        $string = '';
    }
}
