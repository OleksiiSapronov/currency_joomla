<?php /* /home/1255990.cloudwaysapps.com/uwxwemhmnh/public_html/administrator/components/com_akeeba/ViewTemplates/ControlPanel/icons_basic.blade.php */ ?>
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
        <h3><?php echo \Joomla\CMS\Language\Text::_('COM_AKEEBA_CPANEL_HEADER_BASICOPS'); ?></h3>
    </header>

    <div class="akeeba-grid">
	    <?php if($this->permissions['backup']): ?>
            <a class="akeeba-action--green"
               href="index.php?option=com_akeeba&view=Backup">
                <span class="akion-play"></span>
	            <?php echo \Joomla\CMS\Language\Text::_('COM_AKEEBA_BACKUP'); ?>
            </a>
	    <?php endif; ?>

	    <?php if($this->permissions['download'] && AKEEBA_PRO): ?>
            <a class="akeeba-action--green"
                href="index.php?option=com_akeeba&view=Transfer">
                <span class="akion-android-open"></span>
	            <?php echo \Joomla\CMS\Language\Text::_('COM_AKEEBA_TRANSFER'); ?>
            </a>
	    <?php endif; ?>

        <a class="akeeba-action--teal"
            href="index.php?option=com_akeeba&view=Manage">
            <span class="akion-ios-list"></span>
	        <?php echo \Joomla\CMS\Language\Text::_('COM_AKEEBA_BUADMIN'); ?>
        </a>

	    <?php if($this->permissions['configure']): ?>
            <a class="akeeba-action--teal"
                href="index.php?option=com_akeeba&view=Configuration">
                <span class="akion-ios-gear"></span>
	            <?php echo \Joomla\CMS\Language\Text::_('COM_AKEEBA_CONFIG'); ?>
            </a>
	    <?php endif; ?>

	    <?php if($this->permissions['configure']): ?>
            <a class="akeeba-action--teal"
                href="index.php?option=com_akeeba&view=Profiles">
                <span class="akion-person-stalker"></span>
	            <?php echo \Joomla\CMS\Language\Text::_('COM_AKEEBA_PROFILES'); ?>
            </a>
	    <?php endif; ?>
    </div>
</section>
