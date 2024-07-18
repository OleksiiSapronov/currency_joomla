<?php

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die('Restricted Access');

/**
 * @var string $mode
 * @var string $url
 * @var string $pageCacheStatus
 * @var string $task
 * @var string $statusClass
 */
$uri = Uri::getInstance();

if (version_compare(JVERSION, '3.99.99', '<')) {
    ?>
    <div class="btn-group">
        <span class="btn-group separator"></span>
        <span class="icon-cog"></span>
        <?php $route = 'index.php?option=com_jchoptimize&view=ModeSwitcher&task=' . $task . '&return=' . base64_encode($uri); ?>
        <?php echo Text::_('MOD_JCHMODESWITCHER_MODE_TITLE'); ?>:
        <a href="<?php echo $route; ?>"><?php echo $mode; ?></a>
    </div>
<?php } else {
    // Load the Bootstrap Dropdown
    HTMLHelper::_('bootstrap.dropdown', '.dropdown-toggle');

    $hideLinks = $app->input->getBool('hidemainmenu');

    if ($hideLinks) {
        return;
    }

    ?>
    <div class="header-item-content dropdown header-profile jch-modeswitcher">
        <button id="jch-modeswitcher-toggle" class="dropdown-toggle d-flex align-items-center ps-0 py-0" data-bs-toggle="dropdown" type="button"
                title="<?php echo Text::_('MOD_JCHMODESWITCHER'); ?>">
            <div class="header-item-icon">
                <span id="mode-switcher-indicator" class="fa-dot-circle fas d-flex notification-icon <?php echo $statusClass; ?>"
                      aria-hidden="true"></span>
            </div>
            <div class="header-item-text">
                <?php echo Text::_('MOD_JCHMODESWITCHER_TITLE'); ?>
            </div>
            <span class="icon-angle-down" aria-hidden="true"></span>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <?php $route = 'index.php?option=com_jchoptimize&view=ModeSwitcher&task=' . $task . '&return=' . base64_encode(
                            $uri
                    ); ?>
            <a class="dropdown-item" href="<?php echo Route::_($route); ?>">
                <span class="icon-cog icon-fw" aria-hidden="true"></span>
                <?php echo Text::sprintf('MOD_JCHMODESWITCHER_MODE', $mode); ?>
            </a>
            <?php $route = 'index.php?option=com_jchoptimize&view=Utility&task=togglepagecache&return=' . base64_encode(
                            $uri
                    ); ?>
            <a class="dropdown-item" href="<?php echo Route::_($route); ?>">
                <span class="icon-archive icon-fw" aria-hidden="true"></span>
                <span id="page-cache-status">
                    <?php echo Text::sprintf('MOD_JCHMODESWITCHER_PAGECACHE_STATUS', $pageCacheStatus); ?>
                </span>
            </a>

            <div class="dropdown-header">
                    <span class="icon-info-circle icon-fw" aria-hidden="true"></span>
                    <?php echo Text::_('MOD_JCHMODESWITCHER_CACHE_INFO'); ?>
                <div class="ms-5"><em>
                        <span><?php echo Text::_('MOD_JCHMODESWITCHER_FILES'); ?></span> &nbsp;
                        <span class="numFiles-container"><img src="<?php echo Uri::root(true) . '/media/com_jchoptimize/core/images/loader.gif'; ?>"</span>
                    </em>
                </div>
                <div class="ms-5"><em>
                        <span><?php echo Text::_('MOD_JCHMODESWITCHER_SIZE') ?></span> &nbsp;
                        <span class="fileSize-container"><img src="<?php echo Uri::root(true) . '/media/com_jchoptimize/core/images/loader.gif'; ?>"</span>
                    </em>
                </div>
            </div>
            <div class="dropdown-header pt-0">
                <em><small>[<?php echo $pageCachePluginTitle ?>]</small></em>
            </div>
            <?php $route = 'index.php?option=com_jchoptimize&view=Utility&task=cleancache&return=' . base64_encode(
                            $uri
                    ); ?>
            <a class="dropdown-item" href="<?php echo Route::_($route); ?>">
                <span class="fa-trash-alt fas icon-fw" aria-hidden="true"></span>
                <?php echo Text::_('MOD_JCHMODESWITCHER_DELETE_CACHE'); ?>
            </a>
        </div>
    </div>

    <?php
}
?>
