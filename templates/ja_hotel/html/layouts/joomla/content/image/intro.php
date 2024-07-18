<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$item  = is_array($displayData) ? $displayData['item'] : $displayData;    
$params  = $item->params;   
$images = json_decode($item->images);
$imgfloat = (empty($images->float_intro)) ? $params->get('float_intro') : $images->float_intro;

$data = array();
if (isset($images->image_intro) && !empty($images->image_intro)) {
	$data['image'] = $images->image_intro;
	$data['alt'] = $images->image_intro_alt;
	$data['caption'] = $images->image_intro_caption;	
}
if (is_array($displayData) && isset($displayData['img-size'])) $data['size'] = $displayData['img-size'];

if ($params->get('show_readmore') && !empty($item->readmore)) :
	if ($item->params->get('access-view')) :
		$link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid));
	else :
		$menu = JFactory::getApplication()->getMenu();
		$active = $menu->getActive();
		$itemId = $active->id;
		$link1 = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId);
		$returnURL = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid));
		$link = new JUri($link1);
		$link->setVar('return', base64_encode($returnURL));
	endif;
endif;
?>
<?php if (isset($images->image_intro) && !empty($images->image_intro)) : ?>
<div class="pull-<?php echo htmlspecialchars($imgfloat); ?> item-image">
<a href="<?php echo $link; ?>">
<?php echo JLayoutHelper::render('joomla.content.image.image', $data); ?>
</a>
</div>
<?php endif ?>