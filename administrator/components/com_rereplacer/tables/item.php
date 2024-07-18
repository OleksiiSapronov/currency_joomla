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

use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Table\Table as JTable;

/**
 * Item Table
 */
class ReReplacerTableItem extends JTable
{
    /**
     * Constructor
     *
     * @param object    Database object
     *
     * @return    void
     */
    public function __construct(&$db)
    {
        parent::__construct('#__rereplacer', 'id', $db);
    }

    /**
     * Overloaded check function
     *
     * @return boolean
     */
    public function check()
    {
        $this->name   = trim($this->name);
        $this->search = trim($this->search);

        // Check for valid name
        if (empty($this->name))
        {
            $this->setError(JText::_('RR_THE_ITEM_MUST_HAVE_A_NAME'));

            return false;
        }

        // Check for valid search
        if (strpos($this->params, '"use_xml":"1"') !== false)
        {
            if (strpos($this->params, '"xml":""') !== false)
            {
                $this->setError(JText::_('RR_THE_ITEM_MUST_HAVE_AN_XML_FILE'));

                return false;
            }

            return true;
        }

        if (trim($this->search) == '')
        {
            $this->setError(JText::_('RR_THE_ITEM_MUST_HAVE_SOMETHING_TO_SEARCH_FOR'));

            return false;
        }

        return true;
    }
}
