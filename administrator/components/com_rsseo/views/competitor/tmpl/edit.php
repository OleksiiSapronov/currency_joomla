<?php
/**
* @package RSSeo!
* @copyright (C) 2016 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive'); ?>

<form action="<?php echo JRoute::_('index.php?option=com_rsseo&view=competitor&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off" class="form-validate form-horizontal">
	
	<?php echo $this->form->renderField('name'); ?>
	<?php echo $this->form->renderField('tags'); ?>

	<?php echo JHTML::_('form.token'); ?>
	<input type="hidden" name="task" value="" />
	<?php echo $this->form->getInput('id'); ?>
</form>