<?php
/**
 * @package         Advanced Module Manager
 * @version         9.9.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

/**
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Router\Route as JRoute;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('formbehavior.chosen', 'select');

$function  = JFactory::getApplication()->input->getCmd('function', 'jSelectPosition');
$lang      = JFactory::getLanguage();
$ordering  = $this->escape($this->state->get('list.ordering'));
$direction = $this->escape($this->state->get('list.direction'));
$clientId  = $this->state->get('client_id');
$state     = $this->state->get('filter.state');
$template  = $this->state->get('filter.template');
$type      = $this->state->get('filter.type');
?>
<form
    action="<?php echo JRoute::_('index.php?option=com_advancedmodules&view=positions&layout=modal&tmpl=component&function=' . $function . '&client_id=' . $clientId); ?>"
    method="post" name="adminForm" id="adminForm">
    <fieldset class="filter clear">
        <div class="left">
            <label for="filter_search">
                <?php echo JText::_('JSEARCH_FILTER_LABEL'); ?>
            </label>
            <input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" size="30"
                   title="<?php echo JText::_('COM_MODULES_FILTER_SEARCH_DESC'); ?>">

            <button type="submit">
                <?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <button type="button" onclick="document.getElementById('filter_search').value='';this.form.submit();">
                <?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
        </div>

        <div class="right">
            <select name="filter_published" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED'); ?></option>
                <?php echo JHtml::_('select.options', JHtml::_('modules.templateStates'), 'value', 'text', $published, true); ?>
            </select>

            <select name="filter_type" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('COM_MODULES_OPTION_SELECT_TYPE'); ?></option>
                <?php echo JHtml::_('select.options', JHtml::_('modules.types'), 'value', 'text', $type, true); ?>
            </select>

            <select name="filter_template" class="inputbox" onchange="this.form.submit()">
                <option value=""><?php echo JText::_('JOPTION_SELECT_TEMPLATE'); ?></option>
                <?php echo JHtml::_('select.options', JHtml::_('modules.templates', $clientId), 'value', 'text', $template, true); ?>
            </select>
        </div>
    </fieldset>

    <table class="adminlist">
        <thead>
            <tr>
                <th class="title" width="20%">
                    <?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'value', $direction, $ordering); ?>
                </th>
                <th>
                    <?php echo JHtml::_('grid.sort', 'COM_MODULES_HEADING_TEMPLATES', 'templates', $direction, $ordering); ?>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="15">
                    <?php echo $this->pagination->getListFooter(); ?>
                </td>
            </tr>
        </tfoot>
        <tbody>
            <?php $i = 1; ?>
            <?php foreach ($this->items as $value => $templates) : ?>
                <tr class="row<?php echo $i = 1 - $i; ?>">
                    <td>
                        <a class="pointer"
                           onclick="if (window.parent){window.parent.<?php echo $function; ?>('<?php echo $value; ?>');}"><?php echo $this->escape($value); ?></a>
                    </td>
                    <td>
                        <?php if ( ! empty($templates)): ?>
                            <a class="pointer" onclick="if (window.parent){window.parent.<?php echo $function; ?>('<?php echo $value; ?>');}">
                                <ul>
                                    <?php foreach ($templates as $template => $label): ?>
                                        <li><?php echo $lang->hasKey($label) ? JText::sprintf('COM_MODULES_MODULE_TEMPLATE_POSITION', JText::_($template), JText::_($label)) : JText::_($template); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="filter_order" value="<?php echo $ordering; ?>" />
        <input type="hidden" name="filter_order_Dir" value="<?php echo $direction; ?>" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
