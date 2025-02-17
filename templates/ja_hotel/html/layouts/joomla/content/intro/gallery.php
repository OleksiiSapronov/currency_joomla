<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$item = $displayData['item'];
$aparams = $displayData['params'];
$positions = $aparams->get('block_position', 0);

$useDefList =
	($aparams->get('show_modify_date') ||
		$aparams->get('show_publish_date') ||
		$aparams->get('show_create_date') ||
		$aparams->get('show_hits') ||
		$aparams->get('show_category') ||
		$aparams->get('show_parent_category') ||
		$aparams->get('show_author'));
$aparams->set('show_category',1);
?>

<div class="magazine-item-media">
	<?php echo JLayoutHelper::render('joomla.content.image.gallery', $displayData); ?>
	<?php $title = $item->category_title; ?>
</div>

<div class="magazine-item-main">
	<?php echo JLayoutHelper::render('joomla.content.blog_style_default_item_title', $item); ?>
</div>