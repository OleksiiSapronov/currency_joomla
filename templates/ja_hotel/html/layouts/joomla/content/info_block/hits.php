<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

?>
			<dd class="hits">
					<i class="fa fa-eye"></i>
					<meta itemprop="interactionCount" content="UserPageVisits:<?php echo $displayData['item']->hits; ?>" />
          <?php if ($displayData['item']->hits <= 1):
					         echo $displayData['item']->hits.' '. JText::_('TPL_COM_CONTENT_ARTICLE_HIT'); 
                else: 
                  echo $displayData['item']->hits.' '. JText::_('TPL_COM_CONTENT_ARTICLE_HITS'); 
                endif;
          ?>
			</dd>