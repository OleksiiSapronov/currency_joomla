<?php

use JchOptimize\ContainerFactory;
use JchOptimize\Model\ModeSwitcher;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted Access');

require_once JPATH_ADMINISTRATOR . '/components/com_jchoptimize/autoload.php';

/** @var Registry $params */

$container = ContainerFactory::getContainer();

$pageCachePlugins = $pageCachePlugins = [
        'jchoptimizepagecache' => 'MOD_JCHMODESWITCHER_JCHOPTIMIZE_SYSTEM_PAGE_CACHE',
        'cache'                => 'MOD_JCHMODESWITCHER_JOOMLA_SYSTEM_CACHE',
        'lscache'              => 'MOD_JCHMODESWITCHER_LITESPEED_CACHE',
        'pagecacheextended'    => 'MOD_JCHMODESWITCHER_PAGE_CACHE_EXTENDED'
];

/** @var ModeSwitcher $modeSwitcher */
$modeSwitcher = $container->get(ModeSwitcher::class);
$integratedPageCache = $modeSwitcher->getIntegratedPageCachePlugin();
$pageCachePluginTitle = Text::_($pageCachePlugins[$integratedPageCache]);

[$mode, $task, $pageCacheStatus, $statusClass] = $modeSwitcher->getIndicators();
//load media files
$options = [
        'version' => JCH_VERSION
];

if (version_compare(JVERSION, '4.0', '>=') && !$app->input->getBool('hidemainmenu')) {
    $document = Factory::getDocument();
    $document->addStyleSheet(Uri::root(true) . '/media/mod_jchmodeswitcher/css/modeswitcher.css', $options);
    $document->addScript(Uri::root(true) . '/media/com_jchoptimize/js/platform-joomla.js', $options);
    $script = <<<JS

window.addEventListener('DOMContentLoaded', event => {
    const modeSwitcherButton = document.getElementById('jch-modeswitcher-toggle')
    if (modeSwitcherButton !== null){
        modeSwitcherButton.addEventListener('show.bs.dropdown', event => {
            jchPlatform.getCacheInfo();
        });
    }
});
JS;
    $document->addScriptDeclaration($script);
}

require ModuleHelper::getLayoutPath('mod_jchmodeswitcher', $params->get('layout', 'default'));
