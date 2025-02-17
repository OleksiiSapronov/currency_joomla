<?php
/**
 * @package         Advanced Module Manager
 * @version         9.9.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

/**
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\HTML\HTMLHelper as JHtml;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Router\Route as JRoute;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('bootstrap.popover');
?>

<h2><?php echo JText::_('COM_MODULES_TYPE_CHOOSE') ?></h2>
<ul id="new-modules-list" class="list list-striped">
    <?php foreach ($this->items as &$item) : ?>
        <?php
        // Prepare variables for the link.

        $link       = 'index.php?option=com_advancedmodules&task=module.add&eid=' . $item->extension_id;
        $name       = $this->escape($item->name);
        $desc       = JHtml::_('string.truncate', ($this->escape($item->desc)), 200);
        $short_desc = JHtml::_('string.truncate', ($this->escape($item->desc)), 90);
        ?>
        <?php if (JFactory::getDocument()->direction != "rtl") : ?>
            <li>
                <a href="<?php echo JRoute::_($link); ?>">
                    <strong><?php echo $name; ?></strong>
                </a>
                <small class="hasPopover" data-placement="right" title="<?php echo $name; ?>"
                       data-content="<?php echo $desc; ?>"><?php echo $short_desc; ?></small>
            </li>
        <?php else : ?>
            <li>
                <small rel="popover" data-placement="left" title="<?php echo $name; ?>" data-content="<?php echo $desc; ?>"><?php echo $short_desc; ?></small>
                <a href="<?php echo JRoute::_($link); ?>">
                    <strong><?php echo $name; ?></strong>
                </a>
            </li>
        <?php endif ?>
    <?php endforeach; ?>
</ul>
<div class="clr"></div>
