<?php
/*
 * @package 	RSFirewall!
 * @copyright 	(c) 2009 - 2024 RSJoomla!
 * @link 		https://www.rsjoomla.com/joomla-extensions/joomla-security.html
 * @license 	GNU General Public License https://www.gnu.org/licenses/gpl-3.0.en.html
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

Text::script('COM_RSFIREWALL_BUTTON_FAILED');
Text::script('COM_RSFIREWALL_BUTTON_PROCESSING');
Text::script('COM_RSFIREWALL_BUTTON_SUCCESS');
Text::script('COM_RSFIREWALL_CONFIRM_UNIGNORE');
?>
<script>
    function RSFirewallRemoveIgnoredFile($id) {
        if (!confirm(Joomla.JText._('COM_RSFIREWALL_CONFIRM_UNIGNORE'))) {
            return false;
        }
        jQuery.ajax({
            type      : 'POST',
            dataType  : 'JSON',
            url       : 'index.php?option=com_rsfirewall',
            data      : {
                task         : 'ignored.removeFromIgnored',
                ignoredFileId: $id
            },
            beforeSend: function () {
                $button = jQuery('#removeIgnored' + $id);
                $button.attr('disabled', 'true').addClass('btn-processing');
                $button.html('<span class="icon-refresh"></span> ' + Joomla.JText._("COM_RSFIREWALL_BUTTON_PROCESSING"));
            },
            success   : function (result) {
                $button = jQuery('#removeIgnored' + $id);

                if (result.status == true) {
                    $button.removeClass('btn-processing').addClass('btn-success');
                    $button.html('<span class="icon-checkmark-2"></span> ' + Joomla.JText._("COM_RSFIREWALL_BUTTON_SUCCESS"));
                    $button.parents('tr').hide('fast');

                } else {
                    $button.removeClass('btn-processing').addClass('btn-failed');
                    $button.html('<span class="icon-cancel-circle"></span> ' + Joomla.JText._("COM_RSFIREWALL_BUTTON_FAILED"));
                }
            }
        })
    }
</script>

<div class="com-rsfirewall-page-wrapper" style="padding:50px;">
	<div class="alert alert-warning">
		<p><?php echo Text::_('COM_RSFIREWALL_IGNORED_FILES_ALERT_WARNING'); ?></p>
	</div>
	<h3><?php echo Text::_('COM_RSFIREWALL_IGNORED_FILE_TITLE') ?></h3>
	<table id="com-rsfirewall-joomla-configuration-table" class="table table-striped">
		<thead>
		<tr>
			<th><?php echo Text::_('COM_RSFIREWALL_IGNORED_FILE_DATE'); ?></th>
			<th><?php echo Text::_('COM_RSFIREWALL_IGNORED_FILE_FILE'); ?></th>
			<th><?php echo Text::_('COM_RSFIREWALL_IGNORED_FILE_REASON'); ?></th>
			<th>&shy;</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($this->files as $file)
		{ ?>
			<tr>
				<td>
					<?php echo HTMLHelper::_('date', $file->date); ?>
				</td>
				<td style="width:50%">
					<?php echo $this->escape($file->file); ?>
				</td>
				<td>
					<?php echo Text::_('COM_RSFIREWALL_IGNORED_FILE_FLAG'.$file->flag); ?>
				</td>
				<td>
					<button class="btn btn-danger" id="removeIgnored<?php echo $file->id ?>" onclick="RSFirewallRemoveIgnoredFile('<?php echo $file->id ?>')"><?php echo Text::_('COM_RSFIREWALL_IGNORED_FILE_DELETE_FROM_DB'); ?></button>
				</td>
			</tr>
		<?php } ?>
		</tbody>
	</table>
</div>


