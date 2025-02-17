<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$weblinkUrl = JStringPunycode::urlToUTF8($this->item->url);

?>
<div class="item-page">
	<meta itemprop="inLanguage" content="<?php echo ($this->item->language === '*') ? JFactory::getConfig()->get('language') : $this->item->language; ?>" />
	<div class="page-header">
		<h2 itemprop="headline">
			<?php echo $this->escape($this->item->title); ?>
		</h2>
	</div>
	<?php // Content is generated by content plugin event "onContentAfterTitle" ?>
	<?php echo $this->item->event->afterDisplayTitle; ?>
	<?php // Content is generated by content plugin event "onContentBeforeDisplay" ?>
	<?php echo $this->item->event->beforeDisplayContent; ?>
	<div itemprop="articleBody">
		<a href="<?php echo $weblinkUrl; ?>" target="_blank" itemprop="url">
			<?php echo $weblinkUrl; ?>
		</a>
		<?php echo $this->item->description; ?>
	</div>
	<?php // Content is generated by content plugin event "onContentAfterDisplay" ?>
	<?php echo $this->item->event->afterDisplayContent; ?>
</div>
