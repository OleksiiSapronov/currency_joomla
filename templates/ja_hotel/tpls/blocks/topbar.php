<?php
/**
 * @package   T3 Blank
 * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
 
 defined('_JEXEC') or die;
 ?>
 
 <!-- TOPBAR -->
 <?php if ($this->countModules('topbar-1') || $this->countModules('topbar-2') ) : ?>
 <div id="t3-topbar" class="wrap t3-topbar">
	<div class="container">
		<?php if ($this->countModules('topbar-1')) : ?>
			<div class="pull-left t3-topbar-1<?php $this->_c('topbar-1') ?>">
				<jdoc:include type="modules" name="<?php $this->_p('topbar-1') ?>" style="raw" />
			</div>
		<?php endif ?>
		
		<?php if ($this->countModules('topbar-2')) : ?>
			<div class="pull-right t3-topbar-1<?php $this->_c('topbar-2') ?>">
				<jdoc:include type="modules" name="<?php $this->_p('topbar-2') ?>" style="raw" />
			</div>
		<?php endif ?>
	</div>
</div>
<?php endif ?>
 <!-- //TOPBAR -->