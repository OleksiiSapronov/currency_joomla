<?php
/*------------------------------------------------------------------------
  Solidres - Hotel booking extension for Joomla
  ------------------------------------------------------------------------
  @Author    Solidres Team
  @Website   http://www.solidres.com
  @Copyright Copyright (C) 2013 - 2015 Solidres. All Rights Reserved.
  @License   GNU General Public License version 3, or later
------------------------------------------------------------------------*/

defined('_JEXEC') or die;
JHtml::_('behavior.formvalidation');
?>

<div id="solidres">
	<div class="row-fluid">
		<div class="span12">
			<div class="navbar dashboard-nav">
				<div class="navbar-inner">
					<ul class="nav">
						<li>
							<a href="<?php echo JRoute::_('index.php?option=com_solidres&view=customer') ?>">
								My reservations
							</a></li>
						<li class="active">
							<a href="<?php echo JRoute::_('index.php?option=com_solidres&task=myprofile.edit&id=' . $this->form->getValue('id')) ?>">
								My profile
							</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>

	<div class="row-fluid">
		<div class="span12">
			<?php echo JToolbar::getInstance('solidrestoolbar')->render('solidrestoolbar');; ?>
		</div>
	</div>
    
	<div id="sr_panel_right" class="sr_form_view">
			<div class="sr-inner">
				<script type="text/javascript">
					Joomla.submitbutton = function(task)
					{
						if (task == 'myprofile.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
						{
							Joomla.submitform(task);
						}
					}
				</script>
			<form enctype="multipart/form-data" action="<?php JRoute::_('index.php?option=com_solidres&task=myprofile.edit&id=' . $this->form->getValue('id')); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">
				<ul class="nav nav-tabs">
					<li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('SR_NEW_GENERAL_INFO')?></a></li>
				</ul>

				<div class="tab-content">
					<div class="tab-pane active" id="general">
						<?php echo $this->loadTemplate('general'); ?>
					</div>
				</div>
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="return" value="<?php echo $this->returnPage; ?>" />
				<?php echo JHtml::_('form.token'); ?>	
			</form>
		</div>
	</div>
	<?php if ($this->showPoweredByLink) : ?>
	<div class="row-fluid">
		<div class="span12 powered">
			<p>Powered by <a href="http://www.solidres.com" target="_blank">Solidres</a></p>
		</div>
	</div>
	<?php endif ?>
</div>