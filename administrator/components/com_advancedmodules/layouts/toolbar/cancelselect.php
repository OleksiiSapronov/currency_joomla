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

use Joomla\CMS\Language\Text as JText;

$text = JText::_('JTOOLBAR_CANCEL');
?>
<button onclick="location.href='index.php?option=com_advancedmodules'" class="btn btn-default" title="<?php echo $text; ?>">
    <span class="icon-remove"></span> <?php echo $text; ?>
</button>
