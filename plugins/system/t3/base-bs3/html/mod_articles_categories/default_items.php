<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_categories
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
if(!class_exists('ContentHelperRoute')){
	if(version_compare(JVERSION, '4', 'ge')){
		abstract class ContentHelperRoute extends \Joomla\Component\content\Site\Helper\RouteHelper{};
	}else{
		JLoader::register('ContentHelperRoute', $com_path . '/helpers/route.php');
	}
}

$input  = $app->input;
$option = $input->getCmd('option');
$view   = $input->getCmd('view');
$id     = $input->getInt('id');

foreach ($list as $item) : ?>
	<li<?php if ($id == $item->id && in_array($view, array('category', 'categories')) && $option == 'com_content') echo ' class="active"'; ?>> <?php $levelup = $item->level - $startLevel - 1; ?>
		<h<?php echo $params->get('item_heading') + $levelup; ?>>
		<a href="<?php echo Route::_(ContentHelperRoute::getCategoryRoute($item->id, $item->language)); ?>">
		<?php echo $item->title; ?>
			<?php if ($params->get('numitems')) : ?>
				(<?php echo $item->numitems; ?>)
			<?php endif; ?>
		</a>
		</h<?php echo $params->get('item_heading') + $levelup; ?>>
		<?php if ($params->get('show_description', 0)) : ?>
			<?php echo HTMLHelper::_('content.prepare', $item->description, $item->getParams(), 'mod_articles_categories.content'); ?>
		<?php endif; ?>
		<?php if ($params->get('show_children', 0) && (($params->get('maxlevel', 0) == 0)
			|| ($params->get('maxlevel') >= ($item->level - $startLevel)))
			&& count($item->getChildren())) : ?>
			<?php echo '<ul>'; ?>
			<?php $temp = $list; ?>
			<?php $list = $item->getChildren(); ?>
			<?php require ModuleHelper::getLayoutPath('mod_articles_categories', $params->get('layout', 'default') . '_items'); ?>
			<?php $list = $temp; ?>
			<?php echo '</ul>'; ?>
		<?php endif; ?>
	</li>
<?php endforeach; ?>
