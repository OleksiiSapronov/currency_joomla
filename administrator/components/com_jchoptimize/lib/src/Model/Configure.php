<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Model;

use _JchOptimizeVendor\Joomla\DI\ContainerAwareInterface;
use _JchOptimizeVendor\Joomla\DI\ContainerAwareTrait;
use _JchOptimizeVendor\Joomla\Model\DatabaseModelInterface;
use _JchOptimizeVendor\Joomla\Model\DatabaseModelTrait;
use _JchOptimizeVendor\Joomla\Model\StatefulModelInterface;
use _JchOptimizeVendor\Joomla\Model\StatefulModelTrait;
use JchOptimize\Core\Admin\Ajax\Ajax as AdminAjax;
use JchOptimize\Core\Admin\Icons;
use JchOptimize\Core\Admin\Json;
use JchOptimize\Core\Exception\ExceptionInterface;
use JchOptimize\Core\PageCache\CaptureCache;
use Joomla\Registry\Registry;

\defined('_JEXEC') or exit('Restricted Access');
class Configure implements DatabaseModelInterface, StatefulModelInterface, ContainerAwareInterface
{
    use DatabaseModelTrait;
    use StatefulModelTrait;
    use \JchOptimize\Model\SaveSettingsTrait;
    use ContainerAwareTrait;
    private \JchOptimize\Model\TogglePlugins $togglePluginsModel;

    public function __construct(Registry $params, TogglePlugins $togglePluginsModel)
    {
        $this->togglePluginsModel = $togglePluginsModel;
        $this->setState($params);
        $this->name = 'configure';
    }

    /**
     * @throws ExceptionInterface
     */
    public function applyAutoSettings(string $autoSetting)
    {
        $aAutoParams = Icons::autoSettingsArrayMap();
        $aSelectedSetting = \array_column($aAutoParams, $autoSetting);

        /** @psalm-var array<string, string>  $aSettingsToApply */
        $aSettingsToApply = \array_combine(\array_keys($aAutoParams), $aSelectedSetting);
        foreach ($aSettingsToApply as $setting => $value) {
            $this->state->set($setting, $value);
        }
        $this->state->set('combine_files_enable', '1');
        $this->saveSettings();
    }

    /**
     * @throws ExceptionInterface
     */
    public function toggleSetting(?string $setting): bool
    {
        if (\is_null($setting)) {
            // @TODO some logging here
            return \false;
        }
        if ('integrated_page_cache_enable' == $setting) {
            try {
                if (JCH_PRO) {
                    /** @var ModeSwitcher $modeSwitcher */
                    $modeSwitcher = $this->container->get(\JchOptimize\Model\ModeSwitcher::class);
                    $modeSwitcher->togglePageCacheState();

                    /** @var CaptureCache $captureCache */
                    $captureCache = $this->container->get(CaptureCache::class);
                    $captureCache->updateHtaccess();
                } else {
                    $this->togglePluginsModel->togglePageCacheState('jchoptimizepagecache');
                }

                return \true;
            } catch (\Exception $e) {
                return \false;
            }
        }
        $iCurrentSetting = (int) $this->state->get($setting);
        $newSetting = (string) \abs($iCurrentSetting - 1);
        if ('pro_reduce_unused_css' == $setting && '1' == $newSetting) {
            $this->state->set('optimizeCssDelivery_enable', '1');
        }
        if ('optimizeCssDelivery_enable' == $setting && '0' == $newSetting) {
            $this->state->set('pro_reduce_unused_css', '0');
        }
        if ('pro_smart_combine' == $setting) {
            if ('1' == $newSetting) {
                /** @var Json $aSCValues */
                $aSCValues = AdminAjax::getInstance('SmartCombine')->run();

                /** @psalm-var array{css: array{array-key, string}, js: array{array-key, string}} $data */
                $data = $aSCValues->data;
                $aValues = \array_merge($data['css'], $data['js']);
                $this->state->set('pro_smart_combine_values', \rawurlencode(\json_encode($aValues)));
            } else {
                $this->state->set('pro_smart_combine_values', '');
            }
        }
        $this->state->set($setting, $newSetting);
        $this->saveSettings();

        return \true;
    }
}
