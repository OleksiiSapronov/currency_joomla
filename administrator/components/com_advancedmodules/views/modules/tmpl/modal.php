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
use Joomla\CMS\Layout\LayoutHelper as JLayoutHelper;
use Joomla\CMS\Router\Route as JRoute;
use Joomla\CMS\Session\Session as JSession;

if (JFactory::getApplication()->isClient('site'))
{
    JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
}

JHtml::_('behavior.core');
JHtml::_('bootstrap.tooltip', '.hasTooltip', ['placement' => 'bottom']);
JHtml::_('bootstrap.popover', '.hasPopover', ['placement' => 'bottom']);
JHtml::_('formbehavior.chosen', 'select');

// Scripts for the modules xtd-button
JHtml::_('behavior.polyfill', ['event'], 'lt IE 9');
JHtml::_('script', 'com_modules/admin-modules-modal.min.js', ['version' => 'auto', 'relative' => true]);

// Special case for the search field tooltip.
$searchFilterDesc = $this->filterForm->getFieldAttribute('search', 'description', null, 'filter');
JHtml::_('bootstrap.tooltip', '#filter_search', ['title' => JText::_($searchFilterDesc), 'placement' => 'bottom']);

$client    = $this->state->get('filter.client_id') ? 'administrator' : 'site';
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$editor    = JFactory::getApplication()->input->get('editor', '', 'cmd');

$link = 'index.php?option=com_advancedmodules&view=modules&layout=modal&tmpl=component';

if ( ! empty($editor))
{
    $link .= '&editor=' . $editor;
}

$link .= '&' . JSession::getFormToken() . '=1';

?>
<div class="container-popup">

    <form action="<?php echo JRoute::_($link); ?>" method="post" name="adminForm" id="adminForm">

        <?php echo JLayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

        <div class="clearfix"></div>

        <?php if (empty($this->items)) : ?>
            <div class="alert alert-no-items">
                <?php echo JText::_('COM_MODULES_MSG_MANAGE_NO_MODULES'); ?>
            </div>
        <?php else : ?>
            <?php $cols = 10; ?>
            <table class="table table-striped" id="moduleList">
                <thead>
                    <tr>
                        <th width="1%" class="nowrap center">
                            <?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
                        </th>
                        <th class="title">
                            <?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
                        </th>
                        <th width="15%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'COM_MODULES_HEADING_POSITION', 'a.position', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'COM_MODULES_HEADING_MODULE', 'name', $listDirn, $listOrder); ?>
                        </th>
                        <?php if ($this->config->show_note == 3) : ?>
                            <?php $cols++; ?>
                            <th class="title">
                                <?php echo JHtml::_('searchtools.sort', 'JGLOBAL_DESCRIPTION', 'a.note', $listDirn, $listOrder); ?>
                            </th>
                        <?php endif; ?>
                        <?php if ($client == 'site') : ?>
                            <?php if ($this->config->use_categories) : ?>
                                <th width="10%" class="nowrap hidden-phone">
                                    <?php echo JHtml::_('searchtools.sort', 'JCATEGORY', 'aa.category', $listDirn, $listOrder); ?>
                                </th>
                            <?php endif; ?>
                            <th width="10%" class="nowrap hidden-phone">
                                <?php echo JHtml::_('searchtools.sort', 'RL_MENU_ITEMS', 'menuid', $listDirn, $listOrder); ?>
                            </th>
                        <?php endif; ?>
                        <th width="10%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'ag.title', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'l.title', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="8">
                            <?php echo $this->pagination->getListFooter(); ?>
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                    <?php
                    $iconStates = [
                        -2 => 'icon-trash',
                        0  => 'icon-unpublish',
                        1  => 'icon-publish',
                        2  => 'icon-archive',
                    ];

                    foreach ($this->items as $i => $item) :
                        ?>
                        <tr class="row<?php echo $i % 2; ?>">
                            <td class="center">
                                <span class="<?php echo $iconStates[$this->escape($item->published)]; ?>"></span>
                            </td>
                            <td class="has-context">
                                <a class="js-module-insert btn btn-small btn-block btn-success" href="#" data-module="<?php echo $item->id; ?>" data-editor="<?php echo $this->escape($editor); ?>">
                                    <?php echo $this->escape($item->title); ?>
                                </a>

                                <?php if ( ! empty($item->note) && $this->config->show_note == 2) : ?>
                                    <div class="small">
                                        <?php echo $this->escape($item->note); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="small hidden-phone">
                                <?php if ($item->position) : ?>
                                    <a class="js-position-insert btn btn-small btn-block btn-warning" href="#" data-position="<?php echo $this->escape($item->position); ?>" data-editor="<?php echo $this->escape($editor); ?>"><?php echo $this->escape($item->position); ?></a>
                                <?php else : ?>
                                    <span class="label"><?php echo JText::_('JNONE'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="small hidden-phone">
                                <?php echo $item->name; ?>
                            </td>
                            <?php if ($this->config->show_note == 3) : ?>
                                <td class="has-context">
                                    <?php echo $this->escape($item->note); ?>
                                </td>
                            <?php endif; ?>
                            <?php if ($client == 'site') : ?>
                                <?php if ($this->config->use_categories) : ?>
                                    <td class="small hidden-phone">
                                        <?php echo $item->category; ?>
                                    </td>
                                <?php endif; ?>
                                <td class="small hidden-phone">
                                    <?php echo $item->menuid; ?>
                                </td>
                            <?php endif; ?>
                            <td class="small hidden-phone">
                                <?php echo $this->escape($item->access_level); ?>
                            </td>
                            <td class="small hidden-phone">
                                <?php if ($item->language == '') : ?>
                                    <?php echo JText::_('JDEFAULT'); ?>
                                <?php elseif ($item->language == '*') : ?>
                                    <?php echo JText::alt('JALL', 'language'); ?>
                                <?php else : ?>
                                    <?php echo $item->language_title ? JHtml::_('image', 'mod_languages/' . $item->language_image . '.gif', $item->language_title, ['title' => $item->language_title], true) . '&nbsp;' . $this->escape($item->language_title) : JText::_('JUNDEFINED'); ?>
                                <?php endif; ?>
                            </td>
                            <td class="hidden-phone">
                                <?php echo (int) $item->id; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <input type="hidden" name="editor" value="<?php echo $editor; ?>" />
        <?php echo JHtml::_('form.token'); ?>

    </form>
</div>
