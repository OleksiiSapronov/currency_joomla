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

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Form\Form as JForm;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\MVC\Model\AdminModel as JModelAdmin;
use Joomla\CMS\Table\Table as JTable;
use RegularLabs\Library\Date as RL_Date;
use RegularLabs\Library\ParametersNew as RL_Parameters;
use RegularLabs\Library\StringHelper as RL_String;

jimport('joomla.application.component.modeladmin');

/**
 * Item Model
 */
class ReReplacerModelItem extends JModelAdmin
{
    /**
     * @var        string    The prefix to use with controller messages.
     */
    protected $text_prefix = 'RL';

    /**
     * Method to activate list.
     *
     * @param array     An array of item ids.
     * @param string    The new URL to set for the rereplacer.
     * @param string    A comment for the rereplacer list.
     *
     * @return    boolean    Returns true on success, false on failure.
     */
    public function activate(&$pks, $name, $search = null, $replace = null)
    {
        // Initialise variables.
        $user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
        $db   = $this->getDbo();

        // Sanitize the ids.
        $pks = ( array ) $pks;
        JArrayHelper::toInteger($pks);

        // Access checks.
        if ( ! $user->authorise('core.admin', 'com_rereplacer'))
        {
            $pks = [];
            $this->setError(JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'));

            return false;
        }

        if ( ! empty($pks))
        {
            // Update the item rows.
            $db->setQuery(
                'UPDATE `#__rereplacer`' .
                ' SET `name` = ' . $db->quote($name) . ', `published` = 1, `search` = ' . $db->quote($search) . ', `replace` = ' . $db->quote($replace) .
                ' WHERE `id` IN ( ' . implode(',', $pks) . ' )'
            );
            $db->execute();

            // Check for a database error.
            $error = $this->_db->getErrorMsg();

            if ($error)
            {
                $this->setError($error);

                return false;
            }
        }

        return true;
    }

    /**
     * Method to copy an item
     *
     * @access    public
     * @return    boolean    True on success
     */
    public function copy($id)
    {
        $item = $this->getItem($id);

        unset($item->_errors);
        $item->id        = 0;
        $item->published = 0;
        $item->name      = JText::sprintf('RL_COPY_OF', $item->name);

        $item = $this->validate(null, (array) $item);

        return ($this->save($item));
    }

    /**
     * Method to get the record form.
     *
     * @param array   $data     Data for the form.
     * @param boolean $loadData True if the form is to load its own data ( default case ), false if not.
     *
     * @return   JForm    A JForm object on success, false on failure
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            'com_rereplacer.item',
            'item',
            [
                'control'   => 'jform',
                'load_data' => $loadData,
            ]
        );

        if (empty($form))
        {
            return false;
        }

        // Modify the form based on access controls.
        if ($this->canEditState((object ) $data) != true)
        {
            // Disable fields for display.
            $form->setFieldAttribute('published', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('published', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Method to get a single record.
     *
     * @return  mixed    Object on success, false on failure.
     */
    public function getItem($pk = null, $getform = 0)
    {
        // Initialise variables.
        $pk    = ( ! empty($pk)) ? $pk : (int) $this->getState($this->getName() . '.id');
        $table = $this->getTable();

        if ($pk > 0)
        {
            // Attempt to load the row.
            $return = $table->load($pk);

            // Check for a table object error.
            if ($return === false && $table->getError())
            {
                $this->setError($table->getError());

                return false;
            }
        }

        // Convert to the JObject before adding other data.
        $properties = $table->getProperties(1);
        $item       = JArrayHelper::toObject($properties, 'JObject');

        $isini  = ((substr($item->params, 0, 1) != '{') && (substr($item->params, -1, 1) != '}'));
        $params = RL_Parameters::getObjectFromRegistry($item->params, JPATH_ADMINISTRATOR . '/components/com_rereplacer/item_params.xml');

        foreach ($params as $key => $val)
        {
            if (isset($item->{$key}))
            {
                continue;
            }

            $item->{$key} = $val;
        }

        unset($item->params);

        if ($isini)
        {
            foreach ($item as $key => $val)
            {
                if (is_string($val))
                {
                    $item->{$key} = stripslashes($val);
                }
            }
        }

        if ($getform)
        {
            $xmlfile = JPATH_ADMINISTRATOR . '/components/com_rereplacer/item_params.xml';
            $params  = new JForm('jform', ['control' => 'jform']);
            $params->loadFile($xmlfile, 1, '//config');
            $params->bind($item);
            $item->form = $params;
        }

        return $item;
    }

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param type      The table type to instantiate
     * @param string    A prefix for the table class name. Optional.
     * @param array     Configuration array for model. Optional.
     *
     * @return    JTable    A database object
     */
    public function getTable($type = 'Item', $prefix = 'ReReplacerTable', $config = [])
    {
        JTable::addIncludePath(dirname(__FILE__, 2) . '/tables');

        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to save the form data.
     *
     * @param array $data The form data.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function save($data)
    {
        $task   = JFactory::getApplication()->input->get('task');
        $params = json_decode($data['params'], true);

        if (is_null($params))
        {
            $params = [];
        }

        // correct the publish date details
        if (isset($params['assignto_date_publish_up']))
        {
            RL_Date::applyTimezone($params['assignto_date_publish_up']);
        }

        if (isset($params['assignto_date_publish_down']))
        {
            RL_Date::applyTimezone($params['assignto_date_publish_down']);
        }

        if (isset($params['assignto_articles_date_date']))
        {
            RL_Date::applyTimezone($params['assignto_articles_date_date']);
        }

        if (isset($params['assignto_articles_date_from']))
        {
            RL_Date::applyTimezone($params['assignto_articles_date_from']);
        }

        if (isset($params['assignto_articles_date_to']))
        {
            RL_Date::applyTimezone($params['assignto_articles_date_to']);
        }

        if ($task == 'save2copy')
        {
            $data['published'] = 0;
        }

        $this->incrementName($data['name'], $data['id']);

        $data['params'] = json_encode($params);

        return parent::save($data);
    }

    /**
     * Method to validate form data.
     */
    public function validate($form, $data, $group = null)
    {
        $newdata = [];

        // Check for valid name
        if (empty($data['name']))
        {
            $this->setError(JText::_('RR_THE_ITEM_MUST_HAVE_A_NAME'));

            return $newdata;
        }

        // Check for valid search
        if ($data['use_xml'])
        {
            if (trim($data['xml']) == '')
            {
                $this->setError(JText::_('RR_THE_ITEM_MUST_HAVE_AN_XML_FILE'));

                return $newdata;
            }
        }
        else
        {
            if (trim($data['search']) == '')
            {
                $this->setError(JText::_('RR_THE_ITEM_MUST_HAVE_SOMETHING_TO_SEARCH_FOR'));

                return $newdata;
            }
        }

        $params = [];
        $this->_db->setQuery('SHOW COLUMNS FROM #__rereplacer');
        $dbkeys = $this->_db->loadObjectList('Field');
        $dbkeys = array_keys($dbkeys);

        foreach ($data as $key => $val)
        {
            if (in_array($key, $dbkeys))
            {
                $newdata[$key] = $val;
                continue;
            }

            $params[$key] = $val;
        }

        $newdata['params'] = json_encode($params);

        return $newdata;
    }

    /**
     * Method to test whether a record can be deleted.
     *
     * @param object $record A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
     */
    protected function canDelete($record)
    {
        if ($record->published != -2)
        {
            return false;
        }

        $user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

        return $user->authorise('core.admin', 'com_rereplacer');
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_rereplacer.edit.item.data', []);

        if (empty($data))
        {
            $data = $this->getItem();
        }

        return $data;
    }

    private function incrementName(&$name, $id = 0)
    {
        while ($this->isNameAvailable($name, $id))
        {
            $name = RL_String::increment($name);
        }
    }

    private function isNameAvailable($name, $id = 0)
    {
        $db = $this->getDbo();

        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__rereplacer')
            ->where($db->quoteName('name') . ' = ' . $db->quote($name))
            ->where($db->quoteName('published') . ' != -2')
            ->setLimit(1);

        if ($id)
        {
            $query->where($db->quoteName('id') . ' != ' . (int) $id);
        }

        $db->setQuery($query);

        return (boolean) $db->loadResult();
    }
}
