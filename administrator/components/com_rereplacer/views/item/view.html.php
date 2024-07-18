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
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\MVC\View\HtmlView as JView;
use Joomla\CMS\Toolbar\Toolbar;
use RegularLabs\Library\ParametersNew as RL_Parameters;

jimport('joomla.application.component.view');

/**
 * Item View
 */
class ReReplacerViewItem extends JView
{
    protected $config;
    protected $form;
    protected $item;
    protected $parameters;
    protected $state;

    /**
     * Display the view
     *
     */
    public function display($tpl = null)
    {
        $this->form   = $this->get('Form');
        $this->item   = $this->_models['item']->getItem(null, 1);
        $this->state  = $this->get('State');
        $this->config = RL_Parameters::getComponent('rereplacer', $this->state->params);

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            throw new Exception(implode("\n", $errors), 500);
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     */
    protected function addToolbar()
    {
        $isNew = ($this->item->id == 0);
        $canDo = ReReplacerHelper::getActions();

        JFactory::getApplication()->input->set('hidemainmenu', true);

        // Set document title
        JFactory::getDocument()->setTitle(JText::_('REREPLACER') . ': ' . JText::_('RL_ITEM'));
        // Set ToolBar title
        JToolbarHelper::title(JText::_('REREPLACER') . ': ' . JText::_('RL_ITEM'), 'rereplacer icon-reglab');

        // If not checked out, can save the item.
        if ($canDo->get('core.edit'))
        {
            JToolbarHelper::apply('item.apply');
            JToolbarHelper::save('item.save');
        }

        if ($canDo->get('core.edit') && $canDo->get('core.create'))
        {
            JToolbarHelper::save2new('item.save2new');
        }

        if ( ! $isNew && $canDo->get('core.create'))
        {
            JToolbarHelper::save2copy('item.save2copy');
        }

        if (empty($this->item->id))
        {
            JToolbarHelper::cancel('item.cancel');
        }
        else
        {
            JToolbarHelper::cancel('item.cancel', 'JTOOLBAR_CLOSE');
        }

        $bar = Toolbar::getInstance('toolbar');
        $bar->appendButton('Popup', 'help', 'RL_DYNAMIC_TAGS', 'index.php?option=com_rereplacer&view=item&layout=help&tmpl=component');
    }

    protected function render(&$form, $name = '')
    {
        $items = [];

        foreach ($form->getFieldset($name) as $field)
        {
            $datashowon = '';

            if ($field->showon)
            {
                $datashowon = ' data-showon=\'' . json_encode(JFormHelper::parseShowOnConditions($field->showon, $field->formControl, $field->group)) . '\'';
            }

            $items[] = '<div class="control-group"' . $datashowon . '><div class="control-label">'
                . $field->label
                . '</div><div class="controls">'
                . $field->input
                . '</div></div>';
        }

        if (empty ($items))
        {
            return '';
        }

        return implode('', $items);
    }
}
