<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Core\FeatureHelpers;

use _JchOptimizeVendor\Joomla\DI\Container;
use JchOptimize\Core\Helper;
use JchOptimize\Core\Html\CacheManager;
use JchOptimize\Core\Html\FilesManager;
use JchOptimize\Core\Html\LinkBuilder;
use JchOptimize\Core\Http2Preload;
use Joomla\Registry\Registry;

\defined('_JCH_EXEC') or exit('Restricted access');
class DynamicJs extends \JchOptimize\Core\FeatureHelpers\AbstractFeatureHelper
{
    /**
     * @var array Array of javascript files/scripts excluded from the Reduce Unused Js feature
     */
    public static array $criticalJs = [];

    /**
     * @var array Javascript files excluded from Reduce Unused but were deferred
     */
    public static array $criticalJsDeferred = [];

    /**
     * @var array Array of Js Urls to load dynamically for Reduce Unused Js feature
     */
    public static array $aJsDynamicUrls = [];

    /**
     * @var array Array of files that should be loaded dynamically
     */
    public static array $dynamicJs = [];

    private CacheManager $cacheManager;

    private LinkBuilder $linkBuilder;

    private FilesManager $filesManager;

    private bool $enable;

    /**
     * @var bool Whether to load the critical JavaScript asynchronously or to defer. If we're excluding modules then it's best to defer
     */
    private bool $loadCriticalJsAsync = \true;

    /**
     * @var bool The reverse of the above
     */
    private bool $loadCriticalJsDefer = \false;

    public function __construct(Container $container, Registry $params, CacheManager $cacheManager, LinkBuilder $linkBuilder, FilesManager $filesManager)
    {
        parent::__construct($container, $params);
        $this->cacheManager = $cacheManager;
        $this->linkBuilder = $linkBuilder;
        $this->filesManager = $filesManager;
        $this->enable = (bool) $this->params->get('pro_reduce_unused_js_enable', '0');
    }

    public function appendCriticalJsToHtml(): void
    {
        if ($this->enable) {
            $criticalJsToCombine = \array_merge(self::$criticalJs, self::$criticalJsDeferred);
            if (!empty($criticalJsToCombine)) {
                $this->cacheManager->getCombinedFiles($criticalJsToCombine, $criticalJsId, 'js');
                $criticalJsUrl = $this->linkBuilder->buildUrl($criticalJsId, 'js');
                // @see http2Preload::add()
                $this->getContainer()->get(Http2Preload::class)->add($criticalJsUrl, 'js');
                // if files were excluded pei better to defer dynamic files
                if ($this->filesManager->jsFilesExcludedPei) {
                    $this->loadCriticalJsAsync = \false;
                    $this->loadCriticalJsDefer = \true;
                }
                if (!$this->params->get('pro_defer_criticalJs', '1')) {
                    $this->loadCriticalJsAsync = \false;
                    $this->loadCriticalJsDefer = \false;
                }
                $criticalJsScript = $this->linkBuilder->getNewJsLink((string) $criticalJsUrl, $this->loadCriticalJsDefer, $this->loadCriticalJsAsync);
                $this->linkBuilder->appendChildToHead($criticalJsScript);
            }
        }
    }

    public function prepareJsDynamicUrls(array $defers): void
    {
        if (empty(self::$dynamicJs) && empty($defers[0])) {
            return;
        }
        // Let's just go ahead and combine all $dynamicJs
        if (!empty(self::$dynamicJs)) {
            foreach (self::$dynamicJs as $dynamicJsGroup) {
                $this->processDynamicScripts($dynamicJsGroup);
            }
        }
        foreach ($defers as $deferGroup) {
            if ('defer' == $deferGroup[0]['attributeType'] || 'async' == $deferGroup[0]['attributeType']) {
                $this->processDynamicScripts($deferGroup, \true);
            } elseif ('nomodule' == $deferGroup[0]['attributeType']) {
                $this->cacheManager->getAppendedFiles([], $deferGroup, $dynamicNomoduleId);
                self::$aJsDynamicUrls[] = ['url' => $this->linkBuilder->buildUrl($dynamicNomoduleId, 'js'), 'module' => \false, 'nomodule' => \true];
            } else {
                foreach ($deferGroup as $deferArray) {
                    // handle module files
                    $this->processModules($deferArray, 'url', 'pro_criticalModules');
                    // handle module scripts
                    $this->processModules($deferArray, 'content', 'pro_criticalModulesScripts');
                }
            }
        }
        // We now add any critical JS to the HEAD section of the document
        $this->appendCriticalJsToHtml();
    }

    /**
     * @param array $jsGroup  Multidimensional array of JavaScript files to load dynamically
     * @param bool  $deferred If we're handling files that were deferred
     */
    private function processDynamicScripts(array $jsGroup, bool $deferred = \false): void
    {
        $jsToCombine = [];
        // Filter out critical deferred scripts
        foreach ($jsGroup as $jsArray) {
            if (!empty($jsArray['url']) && Helper::findExcludes(Helper::getArray($this->params->get('pro_criticalJs', [])), $jsArray['url']) || !empty($jsArray['content']) && Helper::findExcludes(Helper::getArray($this->params->get('pro_criticalScripts', [])), $jsArray['content'])) {
                if ($deferred) {
                    self::$criticalJsDeferred[] = $jsArray;
                } else {
                    self::$criticalJs[] = $jsArray;
                }

                continue;
            }
            $jsToCombine[] = $jsArray;
        }
        if (!empty($jsToCombine)) {
            $this->cacheManager->getAppendedFiles([], $jsToCombine, $dynamicJsId);
            self::$aJsDynamicUrls[] = ['url' => $this->linkBuilder->buildUrl($dynamicJsId, 'js'), 'module' => \false, 'nomodule' => \false];
        }
    }

    /**
     * @param string $type  Whether url or content
     * @param string $param Which params to use to search for excludes
     */
    private function processModules(array $moduleArray, string $type, string $param): void
    {
        if (!empty($moduleArray[$type])) {
            if (Helper::findExcludes(Helper::getArray($this->params->get($param, [])), $moduleArray[$type])) {
                $this->linkBuilder->appendChildToHTML($moduleArray['script'], 'body');
                // Best to defer the critical js instead
                $this->loadCriticalJsAsync = \false;
                $this->loadCriticalJsDefer = \true;
            } else {
                self::$aJsDynamicUrls[] = [$type => $moduleArray[$type], 'module' => \true, 'nomodule' => \false];
            }
        }
    }
}
