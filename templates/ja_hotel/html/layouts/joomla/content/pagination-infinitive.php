<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$params = $displayData['params'];
$pagination = $displayData['pagination'];
$mode = $params->def('pagination_type', 2) == 2 ? 'manual' : 'auto';
?>

<?php if($pagination->get('pages.total') > 1) :?> 

  <?php JFactory::getDocument()->addScript (T3_TEMPLATE_URL . '/js/infinitive-paging.js'); ?>
  
  <div id="infinity-next" class="btn btn-info hide" data-mode="<?php echo $mode ?>" data-pages="<?php echo $pagination->get('pages.total') ?>" data-finishedmsg="<?php echo JText::_('TPL_LOAD_MODULE_AJAX_DONE');?>"><?php echo JText::_('TPL_LOAD_MORE')?><span style="display: none;" class="fa fa-spin fa-circle-o-notch"></span></div>
<?php else:?>
  <div id="infinity-next" class="btn btn-info disabled" data-pages="1"><?php echo JText::_('TPL_LOAD_MODULE_AJAX_DONE');?></div>
<?php endif;?>	
