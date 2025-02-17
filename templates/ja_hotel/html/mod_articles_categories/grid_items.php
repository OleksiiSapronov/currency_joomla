<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_categories
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

foreach ($list as $item) : ?>
	<div <?php if ($_SERVER['PHP_SELF'] == JRoute::_(ContentHelperRoute::getCategoryRoute($item->id))): echo ' class="col col-xs-6 col-sm-4 col-lg-2 active"'; else: echo 'class="col col-xs-12 col-sm-4 col-lg-2"'; endif; ?>> <?php $levelup = $item->level - $startLevel - 1; ?>
		<h<?php echo $params->get('item_heading') + $levelup; ?>>
		<a href="<?php echo JRoute::_(ContentHelperRoute::getCategoryRoute($item->id)); ?>">
		<?php echo $item->title;?>
			<?php if ($params->get('numitems')) : ?>
				(<?php echo $item->numitems; ?>)
			<?php endif; ?>
		</a>
   		</h<?php echo $params->get('item_heading') + $levelup; ?>>

		<?php if ($params->get('show_description', 0)) : ?>
			<div class="text-description"><?php echo JHtml::_('content.prepare', $item->description, $item->getParams(), 'mod_articles_categories.content'); ?></div>
		<?php endif; ?>
		<?php if ($params->get('show_children', 0) && (($params->get('maxlevel', 0) == 0)
			|| ($params->get('maxlevel') >= ($item->level - $startLevel)))
			&& count($item->getChildren())) : ?>
			<?php echo '<ul class="subcategories">'; ?>
			<?php $temp = $list; ?>
			<?php $list = $item->getChildren(); ?>
			<?php require JModuleHelper::getLayoutPath('mod_articles_categories', $params->get('layout', 'default') . '_items'); ?>
			<?php $list = $temp; ?>
			<?php echo '</ul>'; ?>
		<?php endif; ?>
	</div>
<?php endforeach; ?>
