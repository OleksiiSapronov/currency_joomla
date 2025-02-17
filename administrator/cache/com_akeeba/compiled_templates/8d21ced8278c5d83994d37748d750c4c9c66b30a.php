<?php /* /home/1255990.cloudwaysapps.com/uwxwemhmnh/public_html/administrator/components/com_akeeba/ViewTemplates/ControlPanel/default.blade.php */ ?>
<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this \Akeeba\Backup\Admin\View\ControlPanel\Html */

// Protect from unauthorized access
defined('_JEXEC') || die();

?>
<?php /* Display various possible warnings about issues which directly affect the user's experience */ ?>
<?php echo $this->loadAnyTemplate('admin:com_akeeba/ControlPanel/warnings'); ?>

<?php /* Main area */ ?>
<div class="akeeba-container--66-33">
    <?php /* LEFT COLUMN (66% desktop width) */ ?>
    <div>
	    <?php /* Active profile switch */ ?>
	    <?php echo $this->loadAnyTemplate('admin:com_akeeba/ControlPanel/profile'); ?>

	    <?php /* One Click Backup icons */ ?>
	    <?php if( ! (empty($this->quickIconProfiles)) && $this->permissions['backup']): ?>
		    <?php echo $this->loadAnyTemplate('admin:com_akeeba/ControlPanel/oneclick'); ?>
	    <?php endif; ?>

	    <?php /* Basic operations */ ?>
	    <?php echo $this->loadAnyTemplate('admin:com_akeeba/ControlPanel/icons_basic'); ?>

	    <?php /* Core Upgrade */ ?>
	    <?php echo $this->loadAnyTemplate('admin:com_akeeba/ControlPanel/upgrade'); ?>

	    <?php /* Troubleshooting */ ?>
	    <?php echo $this->loadAnyTemplate('admin:com_akeeba/ControlPanel/icons_troubleshooting'); ?>

	    <?php /* Advanced operations */ ?>
	    <?php echo $this->loadAnyTemplate('admin:com_akeeba/ControlPanel/icons_advanced'); ?>

	    <?php /* Include / Exclude data */ ?>
	    <?php if($this->permissions['configure']): ?>
		    <?php echo $this->loadAnyTemplate('admin:com_akeeba/ControlPanel/icons_includeexclude'); ?>
	    <?php endif; ?>
    </div>
    <?php /* RIGHT COLUMN (33% desktop width) */ ?>
    <div>
	    <?php /* Status Summary */ ?>
	    <?php echo $this->loadAnyTemplate('admin:com_akeeba/ControlPanel/sidebar_status'); ?>

	    <?php /* Backup stats */ ?>
	    <?php echo $this->loadAnyTemplate('admin:com_akeeba/ControlPanel/sidebar_backup'); ?>
    </div>

</div>

<?php /* Footer */ ?>
<?php echo $this->loadAnyTemplate('admin:com_akeeba/ControlPanel/footer'); ?>

<?php /* Usage statistics collection IFRAME */ ?>
<?php if($this->statsIframe): ?>
    <?php echo $this->statsIframe; ?>

<?php endif; ?>
