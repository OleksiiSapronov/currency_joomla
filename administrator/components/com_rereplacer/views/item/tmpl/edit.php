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

use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Router\Route as JRoute;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Extension as RL_Extension;

RL_Document::loadFormDependencies();
JHtml::_('behavior.formvalidator');
JText::script('ERROR');
?>
<style>
    #toolbar-popup-help {
        float: right;
    }
</style>

<form action="<?php echo JRoute::_('index.php?option=com_rereplacer&id=' . ( int ) $this->item->id); ?>" method="post"
      name="adminForm" id="item-form" class="form-validate">

    <div class="form-inline form-inline-header">
        <?php echo $this->item->form->renderField('name'); ?>
    </div>

    <div class="row-fluid form-horizontal">
        <div class="span12">
            <?php echo JHtml::_('bootstrap.startTabSet', 'main', ['active' => 'details']); ?>

            <?php echo JHtml::_('bootstrap.addTab', 'main', 'details', JText::_('JDETAILS')); ?>
            <div class="row-fluid form-vertical">
                <div class="span6">
                    <fieldset>
                        <?php echo $this->render($this->item->form, 'search'); ?>
                        <?php echo $this->render($this->item->form, 'replace'); ?>
                        <?php echo $this->render($this->item->form, 'xml'); ?>
                    </fieldset>

                    <p><?php echo JText::sprintf('RR_HELP_ON_REGULAR_EXPRESSIONS', '<a href="index.php?rl_qp=1&folder=media.rereplacer.images&file=popup.php" target="_blank">', '</a>'); ?></p>
                </div>
                <div class="span3">
                    <fieldset><?php echo $this->render($this->item->form, 'options'); ?></fieldset>
                </div>
                <div class="span3">
                    <fieldset><?php echo $this->render($this->item->form, 'details'); ?></fieldset>
                </div>
            </div>
            <?php echo JHtml::_('bootstrap.endTab'); ?>

            <?php echo JHtml::_('bootstrap.addTab', 'main', 'areas', JText::_('RR_SEARCH_AREAS')); ?>
            <fieldset><?php echo $this->render($this->item->form, 'areas'); ?></fieldset>
            <?php echo JHtml::_('bootstrap.endTab'); ?>

            <?php echo JHtml::_('bootstrap.addTab', 'main', 'assignments', JText::_('RL_PUBLISHING_ASSIGNMENTS')); ?>
            <fieldset><?php echo $this->render($this->item->form, 'assignments'); ?></fieldset>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
        </div>
    </div>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="has_easyblog" value="<?php echo RL_Extension::isInstalled('easyblog'); ?>">
    <input type="hidden" name="has_flexicontent" value="<?php echo RL_Extension::isInstalled('flexicontent'); ?>">
    <input type="hidden" name="has_form2content" value="<?php echo RL_Extension::isInstalled('form2content'); ?>">
    <input type="hidden" name="has_k2" value="<?php echo RL_Extension::isInstalled('k2'); ?>">
    <input type="hidden" name="has_zoo" value="<?php echo RL_Extension::isInstalled('zoo'); ?>">
    <input type="hidden" name="has_akeebasubs" value="<?php echo RL_Extension::isInstalled('akeebasubs'); ?>">
    <input type="hidden" name="has_hikashop" value="<?php echo RL_Extension::isInstalled('hikashop'); ?>">
    <input type="hidden" name="has_mijoshop" value="<?php echo RL_Extension::isInstalled('mijoshop'); ?>">
    <input type="hidden" name="has_redshop" value="<?php echo RL_Extension::isInstalled('redshop'); ?>">
    <input type="hidden" name="has_virtuemart" value="<?php echo RL_Extension::isInstalled('virtuemart'); ?>">
    <input type="hidden" name="has_cookieconfirm" value="<?php echo RL_Extension::isInstalled('cookieconfirm'); ?>">
    <?php echo JHtml::_('form.token'); ?>
</form>

<script language="javascript" type="text/javascript">
    jQuery(document).ready(function() {
        if (Joomla.editors.instances['jform_search']) {
            Joomla.editors.instances['jform_search'].focus();
        }
    });

    Joomla.submitbutton = function(task) {
        if ( ! checkFields(task)) {
            return;
        }

        var f = document.getElementById('item-form');

        if (self != top) {
            if (task == 'item.cancel' || task == 'item.save') {
                f.target = '_top';
            } else {
                f.action += '&tmpl=component';
            }
        }
        Joomla.submitform(task, f);
    };

    function checkFields(task) {
        if (task == 'item.cancel') {
            return true;
        }

        var f = document.getElementById('item-form');

        error = {"error": []};

        if (f['jform[name]'].value == '') {
            error.error.unshift('<?php echo JText::_('RR_THE_ITEM_MUST_HAVE_A_NAME', true); ?>');
        }

        if (f['jform[use_xml]'][1].checked) {
            if (f['jform[xml]'].value == '') {
                error.error.unshift('<?php echo JText::_('RR_THE_ITEM_MUST_HAVE_AN_XML_FILE', true); ?>');
            }
        } else {
            var search_editor = Joomla.editors.instances['jform_search'];
            var search_value  = search_editor ? search_editor.getValue() : f['jform[search]'].value.trim();

            if (search_value == '') {
                error.error.unshift('<?php echo JText::_('RR_THE_ITEM_MUST_HAVE_SOMETHING_TO_SEARCH_FOR', true); ?>');
            }
        }

        if (
            (f['jform[between_start]'].value.trim() != '' && f['jform[between_start]'].value.trim().length < 3) ||
            (f['jform[between_end]'].value.trim() != '' && f['jform[between_end]'].value.trim().length < 3)
        ) {
            error.error.unshift('<?php echo sprintf(JText::_('RR_THE_SEARCH_BETWEEN_STRINGS_SHOULD_BE_LONGER', true), 2); ?>');
        }

        if (error.error.length) {
            Joomla.renderMessages(error);
            return false;
        }

        return true;
    }
</script>
