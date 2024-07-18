<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$params  = new JRegistry($displayData);
$image = $params->get('image');
$alt = $params->get('alt');
$caption = $params->get('caption');
$size = $params->get('size');

require_once (JPATH_ROOT . '/plugins/system/jacontenttype/helpers/image.php');

$img = JAContentTypeImageHelper::getImage($image, $size);
if ($caption) $caption = 'class="caption"' . ' title="' . htmlspecialchars($caption) . '"';
?>

<?php if ($img) : ?>
	 <img <?php echo $caption ?>	src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($alt); ?>" itemprop="thumbnailUrl"/>
<?php endif; ?>
