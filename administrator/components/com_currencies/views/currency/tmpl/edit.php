<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_currencys
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "currency.cancel" || document.formvalidator.isValid(document.getElementById("currency-form")))
		{
			Joomla.submitform(task, document.getElementById("currency-form"));
		}
	};
	jQuery(document).ready(function ($){
		$("#jform_type").change(function(){
			if($(this).val() == 1) {
				$("#image").css("display", "none");
				$("#custom").css("display", "block");
			} else {
				$("#image").css("display", "block");
				$("#custom").css("display", "none");
			}
		}).trigger("change");
	});
');
?>

<form action="<?php echo JRoute::_('index.php?option=com_currencies&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="currency-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_CURRENCIES_DETAILS', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				
				<div id="custom">
					<?php echo $this->form->getControlGroup('customcurrencycode'); ?>
				</div>
				<?php
				echo $this->form->getControlGroup('code');
				echo $this->form->getControlGroup('symbol');
				echo $this->form->getControlGroup('article_id');
				echo $this->form->getControlGroup('types');
				?>
			</div>
			<div class="span3">
				<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>


		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
