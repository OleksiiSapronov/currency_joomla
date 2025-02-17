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
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Layout\LayoutHelper as JLayoutHelper;
use Joomla\CMS\Router\Route as JRoute;
use Joomla\CMS\Uri\Uri as JUri;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\License as RL_License;
use RegularLabs\Library\Version as RL_Version;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

RL_Document::style('regularlabs/style.min.css');
RL_Document::style('rereplacer/style.min.css', '13.2.0.p');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
$ordering  = ($listOrder == 'a.ordering');

$editor = JFactory::getEditor();

$user       = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();
$canCreate  = $user->authorise('core.create', 'com_rereplacer');
$canEdit    = $user->authorise('core.edit', 'com_rereplacer');
$canChange  = $user->authorise('core.edit.state', 'com_rereplacer');
$canCheckin = $user->authorise('core.manage', 'com_checkin');
$saveOrder  = ($listOrder == 'a.ordering');
if ($saveOrder)
{
    $saveOrderingUrl = 'index.php?option=com_rereplacer&task=list.saveOrderAjax&tmpl=component';
    JHtml::_('sortablelist.sortable', 'itemList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$cols = 10;
$cols += ($this->config->show_fields ? 2 : 0);
$cols += ($this->hasCategories ? 1 : 0);

// Version check

if ($this->config->show_update_notification)
{
    echo RL_Version::getMessage('REREPLACER');
}
?>
    <form action="<?php echo JRoute::_('index.php?option=com_rereplacer&view=list'); ?>" method="post" name="adminForm" id="adminForm">
        <?php
        // Search tools bar
        echo JLayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
        ?>

        <table class="table table-striped" id="itemList">
            <thead>
                <tr>
                    <th width="1%" class="nowrap center hidden-phone">
                        <?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                    </th>
                    <th width="1%" class="hidden-phone">
                        <?php echo JHtml::_('grid.checkall'); ?>
                    </th>
                    <th width="1%" class="nowrap center">
                        <?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.published', $listDirn, $listOrder); ?>
                    </th>
                    <th class="title">
                        <?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'a.name', $listDirn, $listOrder); ?>
                    </th>
                    <th class="title hidden-phone">
                        <?php echo JHtml::_('searchtools.sort', 'JGLOBAL_DESCRIPTION', 'a.description', $listDirn, $listOrder); ?>
                    </th>
                    <?php if ($this->config->show_fields) : ?>
                        <th class="title hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'RR_SEARCH', 'a.search', $listDirn, $listOrder); ?>
                        </th>
                        <th class="title hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'RR_REPLACE', 'a.replace', $listDirn, $listOrder); ?>
                        </th>
                    <?php endif; ?>
                    <?php if ($this->hasCategories) : ?>
                        <th width="5%" class="nowrap left hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JCATEGORY', 'a.category', $listDirn, $listOrder); ?>
                        </th>
                    <?php endif; ?>
                    <th width="5%" class="nowrap center hidden-phone">
                        <?php echo JText::_('RR_CASE'); ?>
                    </th>
                    <th width="5%" class="nowrap center hidden-phone">
                        <?php echo JText::_('RR_REGEX'); ?>
                    </th>
                    <th width="5%" class="nowrap center hidden-phone">
                        <?php echo JText::_('RR_ADMIN'); ?>
                    </th>
                    <th width="5%" class="nowrap">
                        <?php echo JText::_('RR_AREA'); ?>
                    </th>
                    <th width="1%" class="nowrap center hidden-phone">
                        <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                    </th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="<?php echo $cols; ?>">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <?php if (empty($this->list)): ?>
                    <tr>
                        <td colspan="<?php echo $cols; ?>">
                            <?php echo JText::_('RL_NO_ITEMS_FOUND'); ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($this->list as $i => $item) : ?>
                        <?php
                        $canCheckinItem = ($canCheckin || $item->checked_out == 0 || $item->checked_out == $user->get('id'));
                        $canChangeItem  = ($canChange && $canCheckinItem);

                        if ($item->casesensitive)
                        {
                            $case = '<span class="btn btn-micro disabled" rel="tooltip" title="' . JText::_('RR_CASE_SENSITIVE') . '"><span class="icon-publish"></span></span>';
                        }
                        else
                        {
                            $case = '<span class="btn btn-micro disabled" rel="tooltip" title="' . JText::_('RL_NOT') . ' ' . JText::_('RR_CASE_SENSITIVE') . '"><span class="icon-cancel"></span></span>';
                        }

                        if ($item->regex)
                        {
                            $regex = '<span class="btn btn-micro disabled" rel="tooltip" title="' . JText::_('RR_REGULAR_EXPRESSIONS') . '"><span class="icon-publish"></span></span>';
                        }
                        else
                        {
                            $regex = '<span class="btn btn-micro disabled" rel="tooltip" title="' . JText::_('RL_NOT') . ' ' . JText::_('RR_REGULAR_EXPRESSIONS') . '"><span class="icon-cancel"></span></span>';
                        }

                        if ( ! empty($item->enable_in_admin))
                        {
                            $enable_in_admin = '<span class="btn btn-micro disabled" rel="tooltip" title="' . JText::_('RR_ENABLE_IN_ADMIN') . '"><span class="icon-publish"></span></span>';
                        }
                        else
                        {
                            $enable_in_admin = '<span class="btn btn-micro disabled" rel="tooltip" title="' . JText::_('RL_NOT') . ' ' . JText::_('RR_ENABLE_IN_ADMIN') . '"><span class="icon-cancel"></span></span>';
                        }

                        $area_name = $item->area == 'articles' ? 'CONTENT' : strtoupper($item->area);
                        $area      = JText::_('RR_AREA_' . $area_name . '_SHORT');
                        $area_tip  = JText::_('RR_AREA_' . $area_name);
                        ?>
                        <tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->area; ?>">
                            <td class="order nowrap center hidden-phone">
                                <?php if ($canChange) :
                                    $disableClassName = '';
                                    $disabledLabel = '';

                                    if ( ! $saveOrder) :
                                        $disabledLabel    = JText::_('JORDERINGDISABLED');
                                        $disableClassName = 'inactive tip-top';
                                    endif; ?>
                                    <span class="sortable-handler <?php echo $disableClassName ?>" rel="tooltip" title="<?php echo $disabledLabel ?>">
                                        <span class="icon-menu"></span>
                                    </span>
                                    <input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>"
                                           class="width-20 text-area-order">
                                <?php else : ?>
                                    <span class="sortable-handler inactive">
                                        <span class="icon-menu"></span>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="center hidden-phone">
                                <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                            </td>
                            <td class="center center">
                                <?php echo JHtml::_('jgrid.published', $item->published, $i, 'list.', $canChangeItem); ?>
                            </td>
                            <td>
                                <?php if ($item->checked_out) : ?>
                                    <?php echo JHtml::_('jgrid.checkedout', $i, $editor, $item->checked_out_time, 'list.', $canCheckin); ?>
                                <?php endif; ?>
                                <?php if ($canEdit) : ?>
                                    <a href="<?php echo JRoute::_('index.php?option=com_rereplacer&task=item.edit&id=' . $item->id); ?>">
                                        <?php echo $this->escape(str_replace(JUri::root(), '', $item->name)); ?></a>
                                <?php else : ?>
                                    <?php echo $this->escape(str_replace(JUri::root(), '', $item->name)); ?>
                                <?php endif; ?>
                            </td>
                            <td class="hidden-phone">
                                <?php
                                $description = explode('---', $item->description);
                                $descr       = nl2br($this->escape(trim($description[0])));

                                if (isset($description[1]))
                                {
                                    $descr = '<span rel="tooltip" title="' . makeTooltipSafe(trim($description[1])) . '">' . $descr . '</span>';
                                }

                                echo $descr;
                                ?>
                            </td>
                            <?php if ($this->config->show_fields) : ?>
                                <?php if ($item->use_xml) : ?>
                                    <td class="hidden-phone" colspan="2">
                                        <em>(<?php echo $this->escape(JText::_('RR_USES_XML_FILE')); ?>)</em>
                                    </td>
                                <?php else : ?>
                                    <td class="hidden-phone">
                                        <span rel="tooltip"
                                              title="<?php echo '<strong>' . JText::_('RR_SEARCH') . '</strong><br>' . makeTooltipSafe($item->search); ?>"><?php echo $this->escape(ReReplacerViewList::maxlen($item->search)); ?></span>
                                    </td>
                                    <td class="hidden-phone">
                                        <span rel="tooltip"
                                              title="<?php echo '<strong>' . JText::_('RR_REPLACE') . '</strong><br>' . makeTooltipSafe($item->replace); ?>"><?php echo $this->escape(ReReplacerViewList::maxlen($item->replace)); ?></span>
                                    </td>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ($this->hasCategories) : ?>
                                <td class="left hidden-phone">
                                    <?php echo $item->category ? '<span class="label label-default">' . $item->category . '</span>' : ''; ?>
                                </td>
                            <?php endif; ?>
                            <td class="center hidden-phone">
                                <?php echo $case; ?>
                            </td>
                            <td class="center hidden-phone">
                                <?php echo $regex; ?>
                            </td>
                            <td class="center hidden-phone">
                                <?php echo $enable_in_admin; ?>
                            </td>
                            <td>
                                <span rel="tooltip" title="<?php echo $area_tip; ?>"><?php echo $area; ?></span>
                            </td>
                            <td class="center hidden-phone">
                                <?php echo (int) $item->id; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <input type="hidden" name="task" value="">
        <input type="hidden" name="boxchecked" value="0">
        <?php echo JHtml::_('form.token'); ?>
    </form>

    <script language="javascript" type="text/javascript">
        Joomla.submitbutton = function(task) {
            var form = document.getElementById("adminForm");
            Joomla.submitform(task, form);

            form.task.value = '';
        };
    </script>

<?php

// Copyright
echo RL_Version::getFooter('REREPLACER', $this->config->show_copyright);

function makeTooltipSafe($str)
{
    return str_replace(
        ['"', '::', "&lt;", "\n"],
        ['&quot;', '&#58;&#58;', "&amp;lt;", '<br>'],
        htmlentities(trim($str), ENT_QUOTES, 'UTF-8')
    );
}
