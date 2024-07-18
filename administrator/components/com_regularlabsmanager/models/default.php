<?php
/**
 * @package         Regular Labs Extension Manager
 * @version         8.5.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper as JApplicationHelper;
use Joomla\CMS\Component\ComponentHelper as JComponentHelper;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\MVC\Model\ListModel as JModelList;
use Joomla\CMS\Table\Table as JTable;
use RegularLabs\Library\Language as RL_Language;
use RegularLabs\Library\RegEx as RL_RegEx;

jimport('joomla.application.component.modellist');

/**
 * Default Model
 */
class RegularLabsManagerModelDefault extends JModelList
{
    /**
     * Get the extensions data
     */
    public function getItems($ids = [])
    {
        $rows = $this->getItemsByXML();

        if (empty($rows))
        {
            return [];
        }

        $items = [];

        foreach ($rows as $row)
        {
            $item = $this->initItem();

            if ( ! isset($row['name']))
            {
                continue;
            }

            $item->name = $row['name'];
            $item->id   = isset($row['id'])
                ? $row['id']
                : RL_RegEx::replace('[^a-z\-]', '', str_replace('?', '-', strtolower($item->name)));

            if ( ! empty($ids) && ! in_array($item->id, $ids))
            {
                continue;
            }

            $item->alias   = $row['alias'] ?? $item->id;
            $item->element = $row['element'] ?? $item->alias;
            $item->types   = [];

            $types = [];

            if (isset($row['type']))
            {
                $types = explode(',', $row['type']);
            }

            $this->checkInstalled($item, $types);

            if (isset($row['old']) && $row['old'] && ! $item->installed)
            {
                continue;
            }

            $item->error = $row['error'] ?? '';

            $items[$item->id] = $item;
        }

        return $items;
    }

    public function storeKey()
    {
        $key = JFactory::getApplication()->input->get('key');

        $data = JComponentHelper::getComponent('com_regularlabsmanager');
        $data = json_decode(json_encode($data), true);

        if (is_null($data))
        {
            $data = [];
        }

        $data['params']['key'] = $key;

        $table = JTable::getInstance('extension');
        // Load the previous Data
        if ( ! $table->load($data['id']))
        {
            throw new RuntimeException($table->getError());
        }

        unset($data['id']);

        // Bind the data.
        if ( ! $table->bind($data))
        {
            throw new RuntimeException($table->getError());
        }

        // Check the data.
        if ( ! $table->check())
        {
            throw new RuntimeException($table->getError());
        }

        // Store the data.
        if ( ! $table->store())
        {
            throw new RuntimeException($table->getError());
        }

        $db = JFactory::getDbo();

        // First, remove the &pro=1 from all regularlabs.com urls

        $query = $db->getQuery(true)
            ->update('#__update_sites')
            ->set($db->quoteName('location')
                . ' = replace(' . $db->quoteName('location') . ', ' . $db->quote('&pro=1') . ', ' . $db->quote('') . ')')
            ->where($db->quoteName('location') . ' LIKE ' . $db->quote('%download.regularlabs.com%'));
        $db->setQuery($query);
        $db->execute();

        // Place back the &pro=1 on all regularlabs.com urls
        // And add the key

        $extra_query = $key ? 'k=' . $key : '';

        $query->clear()
            ->update('#__update_sites')
            ->set($db->quoteName('location')
                . ' = replace(' . $db->quoteName('location') . ', ' . $db->quote('&type=') . ', ' . $db->quote('&pro=1&type=') . ')')
            ->set($db->quoteName('extra_query') . ' = ' . $db->quote($extra_query))
            ->where($db->quoteName('location') . ' LIKE ' . $db->quote('%download.regularlabs.com%'));
        $db->setQuery($query);
        $db->execute();

        JFactory::getCache()->clean('_system');
    }

    /**
     * Return an empty extension item
     */
    private function checkInstalled(&$item, $types = [])
    {
        jimport('joomla.filesystem.file');

        $file = '';

        [$xml, $client_id] = $this->getXML('pkg', $item->element);

        if ($xml)
        {
            $el            = $this->initType();
            $el->type      = 'pkg';
            $el->client_id = $client_id;
            $el->link      = '';
            $el->id        = $this->getID('pkg', $item->element);

            $item->types['pkg'] = $el;

            $file = $xml;
        }

        foreach ($types as $type)
        {
            $el       = $this->initType();
            $el->type = $type;
            [$xml, $client_id] = $this->getXML($type, $item->element);

            if ( ! $xml)
            {
                switch ($item->element)
                {
                    case 'tabs':
                        [$xml, $client_id] = $this->getXML($type, 'tabber');
                        break;
                    case 'sliders':
                        [$xml, $client_id] = $this->getXML($type, 'slider');
                        break;
                }
            }

            $el->client_id = $client_id;
            $el->link      = $this->getURL($type, $item->element, $client_id);

            if ( ! $xml)
            {
                $item->missing[]    = $type;
                $item->types[$type] = $el;
                continue;
            }

            $el->id = $this->getID($type, $item->element);

            if ( ! $file)
            {
                $file = $xml;
            }

            $item->types[$type] = $el;
        }

        if ( ! $file)
        {
            $item->missing = [];

            return;
        }

        $xml = JApplicationHelper::parseXMLInstallFile($file);

        if (empty($xml) || ! isset($xml['version']))
        {
            return;
        }

        // Fix wrong version numbers
        $xml['version'] = str_replace(
            ['4.4.0PROFREE', '4.4.0PROPRO', 'FREEFREE', 'FREEPRO', 'PROFREE', 'PROPRO'],
            ['1.0.1FREE', '1.0.1PRO', 'FREE', 'PRO', 'FREE', 'PRO'],
            $xml['version']
        );

        $item->installed = 1;
        $item->version   = str_replace(['FREE', 'PRO'], '', $xml['version']);

        if (stripos($xml['version'], 'PRO') !== false)
        {
            $item->pro = 1;
        }

        if ( ! $item->version)
        {
            $item->version = '0.0.0';
        }
    }

    /**
     * Get the extension id
     */
    private function getID($type, $element)
    {
        $db = JFactory::getDbo();

        [$type, $folder] = explode('_', $type . '_');

        $query = $db->getQuery(true)
            ->from('#__extensions as e')
            ->select('e.extension_id');

        switch ($type)
        {
            case 'pkg':
                $query->where('e.type = ' . $db->quote('package'))
                    ->where('e.element = ' . $db->quote('pkg_' . $element));
                break;
            case 'com':
                $query->where('e.type = ' . $db->quote('component'))
                    ->where('e.element = ' . $db->quote('com_' . $element));
                break;
            case 'mod':
                $query->where('e.type = ' . $db->quote('module'))
                    ->where('e.element = ' . $db->quote('mod_' . $element));
                break;
            case 'plg':
                $query->where('e.type = ' . $db->quote('plugin'))
                    ->where('e.element = ' . $db->quote($element))
                    ->where('e.folder = ' . $db->quote($folder));
                break;

            default:
                return 0;
        }

        $db->setQuery($query);

        return $db->loadResult();
    }

    /**
     * Return an object list with items from the xml file
     */
    private function getItemsByXML()
    {
        // Get a storage key.
        $store = $this->getStoreId();

        // Try to load the data from internal storage.
        if (isset($this->cache[$store]))
        {
            //return $this->cache[$store];
        }

        $items = [];

        jimport('joomla.filesystem.file');

        if ( ! file_exists(JPATH_COMPONENT . '/extensions.xml'))
        {
            return $items;
        }

        $file = file_get_contents(JPATH_COMPONENT . '/extensions.xml');

        if ( ! $file)
        {
            return $items;
        }

        $xml_parser = xml_parser_create();
        xml_parse_into_struct($xml_parser, $file, $fields);
        xml_parser_free($xml_parser);

        foreach ($fields as $field)
        {
            if (
                $field['tag'] != 'EXTENSION'
                || ! isset($field['attributes'])
            )
            {
                continue;
            }

            $item = [];

            foreach ($field['attributes'] as $val => $key)
            {
                $item[strtolower($val)] = $key;
            }

            $items[] = $item;
        }

        // Add the items to the internal cache.
        $this->cache[$store] = $items;

        return $this->cache[$store];
    }

    /**
     * Get the extension url
     */
    private function getURL($type, $element, $client_id = 1)
    {
        [$type, $folder] = explode('_', $type . '_');

        switch ($type)
        {
            case 'com':
                RL_Language::load('com_' . $element . '.sys', '', true);

                return 'option=com_' . $element;

            case 'mod':
                RL_Language::load('mod_' . $element . '.sys', '', true);

                return 'option=com_modules&client_id=' . $client_id
                    . '&filter_module=mod_' . $element . '&filter_search=';

            case 'plg':
                $db    = JFactory::getDbo();
                $query = $db->getQuery(true)
                    ->select('name')
                    ->from('#__extensions')
                    ->where('type =' . $db->quote('plugin'))
                    ->where('folder =' . $db->quote($folder))
                    ->where('element =' . $db->quote($element));

                $name = $db->setQuery($query)->loadResult();

                RL_Language::load('plg_' . $folder . '_' . $element . '.sys', '', true);
                $name = JText::_($name);
                $name = RL_RegEx::replace('^(.*?)\?.*$', '\1', $name);

                return 'option=com_plugins&filter_folder=&filter_search=' . $name;

            default:
                return '';
        }
    }

    /**
     * Get the extension url
     */
    private function getXML($type, $element)
    {
        if ($type == 'pkg')
        {
            $xml = JPATH_ADMINISTRATOR . '/manifests/packages/pkg_' . $element . '.xml';

            if ( ! file_exists($xml))
            {
                return ['', 1];
            }

            return [$xml, 1];
        }

        if ($type == 'com')
        {
            $xml = JPATH_ADMINISTRATOR . '/components/com_' . $element . '/' . $element . '.xml';

            if (file_exists($xml))
            {
                return [$xml, 1];
            }

            $xml = JPATH_SITE . '/components/com_' . $element . '/' . $element . '.xml';

            if (file_exists($xml))
            {
                return [$xml, 0];
            }

            $xml = JPATH_ADMINISTRATOR . '/components/com_' . $element . '/com_' . $element . '.xml';

            if (file_exists($xml))
            {
                return [$xml, 1];
            }

            $xml = JPATH_SITE . '/components/com_' . $element . '/com_' . $element . '.xml';

            if (file_exists($xml))
            {
                return [$xml, 0];
            }

            return ['', 1];
        }

        if ($type == 'mod')
        {
            $xml = JPATH_ADMINISTRATOR . '/modules/mod_' . $element . '/' . $element . '.xml';

            if (file_exists($xml))
            {
                return [$xml, 1];
            }

            $xml = JPATH_SITE . '/modules/mod_' . $element . '/' . $element . '.xml';

            if (file_exists($xml))
            {
                return [$xml, 0];
            }

            $xml = JPATH_ADMINISTRATOR . '/modules/mod_' . $element . '/mod_' . $element . '.xml';

            if (file_exists($xml))
            {
                return [$xml, 1];
            }

            $xml = JPATH_SITE . '/modules/mod_' . $element . '/mod_' . $element . '.xml';

            if (file_exists($xml))
            {
                return [$xml, 0];
            }

            return ['', 1];
        }

        if (substr($type, 0, 4) == 'plg_')
        {
            $plg_type = substr($type, 4);

            $xml = JPATH_PLUGINS . '/' . $plg_type . '/' . $element . '/' . $element . '.xml';

            if (file_exists($xml))
            {
                return [$xml, 1];
            }

            $xml = JPATH_PLUGINS . '/' . $plg_type . '/' . $element . '.xml';

            if (file_exists($xml))
            {
                return [$xml, 1];
            }

            return ['', 1];
        }

        return ['', 1];
    }

    /**
     * Return an empty extension item object
     */
    private function initItem()
    {
        $item            = (object) [];
        $item->id        = 0;
        $item->name      = '';
        $item->alias     = '';
        $item->element   = '';
        $item->installed = '';
        $item->version   = '';
        $item->pro       = 0;
        $item->haspro    = 1;
        $item->types     = [];
        $item->missing   = [];

        return $item;
    }

    /**
     * Return an empty type object
     */
    private function initType()
    {
        $item       = (object) [];
        $item->id   = 0;
        $item->type = '';
        $item->link = '';

        return $item;
    }
}
