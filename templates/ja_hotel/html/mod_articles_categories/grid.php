<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_categories
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="row categories-module<?php echo $moduleclass_sfx; ?> <?php if ($params->get('show_description', 0)) : ?>descriptions<?php endif; ?>">
<?php require JModuleHelper::getLayoutPath('mod_articles_categories', $params->get('layout', 'grid') . '_items'); ?>
</div>
