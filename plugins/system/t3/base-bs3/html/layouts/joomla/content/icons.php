<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;

$canEdit = $displayData['params']->get('access-edit');
$articleId = $displayData['item']->id;
if(version_compare(JVERSION, '4', 'lt')):

HTMLHelper::_('bootstrap.framework');
?>
	<?php if (empty($displayData['print'])) : ?>

		<?php if ($canEdit || $displayData['params']->get('show_print_icon') || $displayData['params']->get('show_email_icon')) : ?>
			<div class="btn-group pull-right">
				<a class="btn dropdown-toggle" data-toggle="dropdown" href="#"> <span class="fa fa-cog"></span> <span class="caret"><span class="element-invisible">Empty</span></span> </a>
				<?php // Note the actions class is deprecated. Use dropdown-menu instead. ?>
				<ul class="dropdown-menu">
					<?php if ($displayData['params']->get('show_print_icon')) : ?>
						<li class="print-icon"> <?php echo HTMLHelper::_('icon.print_popup', $displayData['item'], $displayData['params']); ?> </li>
					<?php endif; ?>
					<?php if ($displayData['params']->get('show_email_icon')) : ?>
						<li class="email-icon"> <?php echo HTMLHelper::_('icon.email', $displayData['item'], $displayData['params']); ?> </li>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
						<li class="edit-icon"> <?php echo HTMLHelper::_('icon.edit', $displayData['item'], $displayData['params']); ?> </li>
					<?php endif; ?>
				</ul>
			</div>
		<?php endif; ?>

	<?php else : ?>

		<div class="pull-right">
			<?php echo HTMLHelper::_('icon.print_screen', $displayData['item'], $displayData['params']); ?>
		</div>

	<?php endif; ?>
<?php else: ?>
	<?php if ($canEdit) : ?>
	<div class="icons float-right float-end">
  	  <div class="edit-link">
	    <?php echo HTMLHelper::_('icon.edit', $displayData['item'], $displayData['params']); ?>
	  </div>
	</div>
<?php endif; ?>

<?php endif; ?>
