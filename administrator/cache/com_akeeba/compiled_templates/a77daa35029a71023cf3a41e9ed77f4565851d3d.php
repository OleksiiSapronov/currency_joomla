<?php /* /home/1255990.cloudwaysapps.com/uwxwemhmnh/public_html/administrator/components/com_akeeba/ViewTemplates/ControlPanel/icons_troubleshooting.blade.php */ ?>
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
<section class="akeeba-panel--info">
    <header class="akeeba-block-header">
        <h3><?php echo \Joomla\CMS\Language\Text::_('COM_AKEEBA_CPANEL_HEADER_TROUBLESHOOTING'); ?></h3>
    </header>

    <div class="akeeba-grid">
	    <?php if($this->permissions['backup']): ?>
            <a class="akeeba-action--teal"
                href="index.php?option=com_akeeba&view=Log">
                <span class="akion-ios-search-strong"></span>
	            <?php echo \Joomla\CMS\Language\Text::_('COM_AKEEBA_LOG'); ?>
            </a>
	    <?php endif; ?>

	    <?php if(AKEEBA_PRO && $this->permissions['configure']): ?>
            <a class="akeeba-action--teal"
                href="index.php?option=com_akeeba&view=Alice">
                <span class="akion-medkit"></span>
	            <?php echo \Joomla\CMS\Language\Text::_('COM_AKEEBA_ALICE'); ?>
            </a>
	    <?php endif; ?>
    </div>
</section>
