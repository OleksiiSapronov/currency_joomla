<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$item  		= $displayData['item']; 
$params  		= $item->params;
$thumbnail 		= $params->get('ctm_thumbnail');
$desc 			= $params->get('ctm_description');

$ctm_source 	= $params->get('ctm_source', 'youtube');

if(!$thumbnail) {
	$images = json_decode($item->images);
	$thumbnail = @$images->image_intro;
}

$data = array();
if (is_array($displayData) && isset($displayData['img-size'])) $data['size'] = $displayData['img-size'];

if(!defined('TELINE_VIDEO_LIST_PLAY')) {
	define('TELINE_VIDEO_LIST_PLAY', 1);

	JHtml::_('jquery.framework');
	$doc = JFactory::getDocument();
}

if (isset($thumbnail) && !empty($thumbnail)) {
	$data['image'] = $thumbnail;
	$data['alt'] = $item->title;
	$data['caption'] = $desc;	
}
?>
<?php if (isset($thumbnail) && !empty($thumbnail)) : ?>
	<div class="item-image ja-video-list"
		 data-url="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($item->id, $item->catid)); ?>"
		 data-title="<?php echo htmlspecialchars($item->title); ?>"
		 data-video="<?php echo htmlspecialchars(JLayoutHelper::render('joomla.content.video_play', array('item' => $item, 'context' => 'list'))); ?>">
		<a class="btn-play" href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($item->id, $item->catid)); ?>">
			<i class="fa fa-play-circle-o"></i>
		</a>
		<?php echo JLayoutHelper::render('joomla.content.image.image', $data); ?>
	</div>
<?php endif; ?>
