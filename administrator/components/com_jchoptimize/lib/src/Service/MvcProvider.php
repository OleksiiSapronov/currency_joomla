<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2021 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Service;

use _JchOptimizeVendor\Joomla\DI\Container;
use _JchOptimizeVendor\Joomla\DI\ServiceProviderInterface;
use _JchOptimizeVendor\Joomla\Renderer\BladeRenderer;
use _JchOptimizeVendor\Joomla\Renderer\RendererInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\StorageInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\TaggableInterface;
use JchOptimize\Controller\Ajax;
use JchOptimize\Controller\ApplyAutoSetting;
use JchOptimize\Controller\CacheInfo;
use JchOptimize\Controller\ControlPanel;
use JchOptimize\Controller\OptimizeImage;
use JchOptimize\Controller\OptimizeImages;
use JchOptimize\Controller\PageCache;
use JchOptimize\Controller\ToggleSetting;
use JchOptimize\Controller\Utility;
use JchOptimize\ControllerResolver;
use JchOptimize\Core\Admin\Icons;
use JchOptimize\Core\Cdn;
use JchOptimize\Core\PageCache\PageCache as CorePageCache;
use JchOptimize\Model\ApiParams;
use JchOptimize\Model\BulkSettings;
use JchOptimize\Model\Cache;
use JchOptimize\Model\Configure;
use JchOptimize\Model\OrderPlugins;
use JchOptimize\Model\PageCache as PageCacheModel;
use JchOptimize\Model\TogglePlugins;
use JchOptimize\Model\Updates;
use JchOptimize\Platform\Paths;
use JchOptimize\View\ControlPanelHtml;
use JchOptimize\View\OptimizeImagesHtml;
use JchOptimize\View\PageCacheHtml;
use Joomla\Application\AbstractApplication;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

\defined('_JEXEC') or exit('Restricted access');
class MvcProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        // MVC Dependencies
        $container->share(Input::class, [$this, 'getInputService'], \true);
        $container->alias(AbstractApplication::class, AdministratorApplication::class)->share(AdministratorApplication::class, [$this, 'getAbstractApplicationService'], \true);
        $container->share(RendererInterface::class, [$this, 'getTemplateRendererService'], \true);
        $container->share(ControllerResolver::class, [$this, 'getControllerResolverService'], \true);
        // controllers
        $container->alias('ControlPanel', ControlPanel::class)->share(ControlPanel::class, [$this, 'getControllerControlPanelService'], \true);
        $container->alias('PageCache', PageCache::class)->share(PageCache::class, [$this, 'getControllerPageCacheService'], \true);
        $container->alias('OptimizeImages', OptimizeImages::class)->share(OptimizeImages::class, [$this, 'getControllerOptimizeImagesService'], \true);
        $container->alias('Ajax', Ajax::class)->share(Ajax::class, [$this, 'getControllerAjaxService'], \true);
        $container->alias('Utility', Utility::class)->share(Utility::class, [$this, 'getControllerUtilityService'], \true);
        $container->alias('ApplyAutoSetting', ApplyAutoSetting::class)->share(ApplyAutoSetting::class, [$this, 'getControllerApplyAutoSettingService'], \true);
        $container->alias('ToggleSetting', ToggleSetting::class)->share(ToggleSetting::class, [$this, 'getControllerToggleSettingService'], \true);
        $container->alias('OptimizeImage', OptimizeImage::class)->share(OptimizeImage::class, [$this, 'getControllerOptimizeImageService'], \true);
        $container->alias('CacheInfo', CacheInfo::class)->share(CacheInfo::class, [$this, 'getControllerCacheInfoService'], \true);
        // Models
        $container->share(Cache::class, [$this, 'getModelCacheService'], \true);
        $container->share(ApiParams::class, [$this, 'getModelApiParamsService'], \true);
        $container->share(OrderPlugins::class, [$this, 'getModelOrderPluginsService'], \true);
        $container->share(Configure::class, [$this, 'getModelConfigureService'], \true);
        $container->share(Updates::class, [$this, 'getModelUpdatesService'], \true);
        $container->share(PageCacheModel::class, [$this, 'getModelPageCacheService'], \true);
        $container->share(BulkSettings::class, [$this, 'getModelBulkSettingsService'], \true);
        $container->share(TogglePlugins::class, [$this, 'getModelTogglePluginsService'], \true);
        // View
        $container->share(ControlPanelHtml::class, [$this, 'getViewControlPanelHtmlService'], \true);
        $container->share(PageCacheHtml::class, [$this, 'getViewPageCacheHtmlService'], \true);
        $container->share(OptimizeImagesHtml::class, [$this, 'getViewOptimizeImagesHtmlService'], \true);
    }

    public function getInputService(): Input
    {
        return new Input($_REQUEST);
    }

    /**
     * @throws \Exception
     */
    public function getAbstractApplicationService(): ?AdministratorApplication
    {
        try {
            // @var AdministratorApplication
            return Factory::getApplication();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getTemplateRendererService(Container $container): RendererInterface
    {
        return (new BladeRenderer($container->get(\_JchOptimizeVendor\Illuminate\View\Factory::class)))->addFolder(Paths::templatePath());
    }

    public function getControllerResolverService(Container $container): ControllerResolver
    {
        return new ControllerResolver($container, $container->get(Input::class));
    }

    public function getControllerControlPanelService(Container $container): ControlPanel
    {
        return (new ControlPanel($container->get(Updates::class), $container->get(ControlPanelHtml::class), $container->get(Icons::class), $container->get(Cdn::class), $container->get(Input::class), $container->get(AdministratorApplication::class)))->setContainer($container);
    }

    public function getControllerPageCacheService(Container $container): PageCache
    {
        return (new PageCache($container->get(PageCacheModel::class), $container->get(PageCacheHtml::class), $container->get(Input::class), $container->get(AdministratorApplication::class)))->setContainer($container);
    }

    public function getControllerOptimizeImagesService(Container $container): OptimizeImages
    {
        return new OptimizeImages($container->get(ApiParams::class), $container->get(OptimizeImagesHtml::class), $container->get(Icons::class));
    }

    public function getControllerAjaxService(Container $container): Ajax
    {
        return new Ajax($container->get(Input::class), $container->get(AdministratorApplication::class));
    }

    public function getControllerUtilityService(Container $container): Utility
    {
        return (new Utility($container->get(OrderPlugins::class), $container->get(Cache::class), $container->get(TogglePlugins::class), $container->get(BulkSettings::class), $container->get(Input::class), $container->get(AdministratorApplication::class)))->setContainer($container);
    }

    public function getControllerApplyAutoSettingService(Container $container): ApplyAutoSetting
    {
        return new ApplyAutoSetting($container->get(Configure::class), $container->get(Input::class), $container->get(AdministratorApplication::class));
    }

    public function getControllerToggleSettingService(Container $container): ToggleSetting
    {
        return (new ToggleSetting($container->get(Configure::class), $container->get(Input::class), $container->get(AdministratorApplication::class)))->setContainer($container);
    }

    public function getControllerOptimizeImageService(Container $container): OptimizeImage
    {
        return new OptimizeImage($container->get(Input::class), $container->get(AdministratorApplication::class));
    }

    public function getControllerCacheInfoService(Container $container): CacheInfo
    {
        return new CacheInfo($container->get(Cache::class), $container->get(Input::class), $container->get(AdministratorApplication::class));
    }

    public function getModelCacheService(Container $container): Cache
    {
        return (new Cache($container->get(StorageInterface::class), $container->get(CorePageCache::class), $container->get(TaggableInterface::class)))->setContainer($container);
    }

    public function getModelApiParamsService(Container $container): ApiParams
    {
        $model = new ApiParams($container->get(Updates::class));
        $model->setState($container->get(Registry::class));

        return $model;
    }

    public function getModelOrderPluginsService(Container $container): OrderPlugins
    {
        $model = new OrderPlugins();
        $model->setDb($container->get(DatabaseInterface::class));

        return $model;
    }

    public function getModelConfigureService(Container $container): Configure
    {
        $model = (new Configure($container->get(Registry::class), $container->get(TogglePlugins::class)))->setContainer($container);
        $model->setDb($container->get(DatabaseInterface::class));

        return $model;
    }

    public function getModelUpdatesService(Container $container): Updates
    {
        return new Updates($container->get(Registry::class), $container->get(DatabaseInterface::class));
    }

    public function getModelPageCacheService(Container $container): PageCacheModel
    {
        return new PageCacheModel($container->get(CorePageCache::class), $container);
    }

    public function getModelBulkSettingsService(Container $container): BulkSettings
    {
        $model = new BulkSettings($container->get(Registry::class));
        $model->setDb($container->get(DatabaseInterface::class));

        return $model;
    }

    public function getModelTogglePluginsService(Container $container): TogglePlugins
    {
        $model = new TogglePlugins();
        $model->setDb($container->get(DatabaseInterface::class));

        return $model;
    }

    public function getViewControlPanelHtmlService(Container $container): ControlPanelHtml
    {
        return (new ControlPanelHtml($container->get(RendererInterface::class)))->setLayout('control_panel');
    }

    public function getViewPageCacheHtmlService(Container $container): PageCacheHtml
    {
        return (new PageCacheHtml($container->get(RendererInterface::class)))->setLayout('page_cache');
    }

    public function getViewOptimizeImagesHtmlService(Container $container): OptimizeImagesHtml
    {
        return (new OptimizeImagesHtml($container->get(RendererInterface::class)))->setLayout('optimize_images');
    }
}
