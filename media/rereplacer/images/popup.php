<?php
/**
 * @package         ReReplacer
 * @version         13.2.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri as JUri;

echo '<img src="' . JUri::root(true) . '/media/rereplacer/images/regular-expressions-cheat-sheet-v2.png">';
die;
