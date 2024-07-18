<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JHtml::addIncludePath(JPATH_ROOT . '/components/com_content/helpers');
?>
      <dd class="edit-icon hasTooltip">
        <?php echo JHtml::_('icon.edit', $displayData['item'], $displayData['params']); ?>
      </dd>