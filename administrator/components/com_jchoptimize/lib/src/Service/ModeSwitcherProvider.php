<?php

/**
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

namespace JchOptimize\Service;

use _JchOptimizeVendor\Joomla\DI\Container;
use _JchOptimizeVendor\Joomla\DI\ServiceProviderInterface;
use JchOptimize\Controller\ModeSwitcher as ModeSwitcherController;
use JchOptimize\Model\Cache;
use JchOptimize\Model\ModeSwitcher as ModeSwitcherModel;
use JchOptimize\Model\TogglePlugins;
use Joomla\Application\AbstractApplication;
use Joomla\Database\DatabaseInterface;
use Joomla\Input\Input;
use Joomla\Registry\Registry;

\defined('_JCH_EXEC') or exit('Restricted access');
class ModeSwitcherProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->alias('ModeSwitcher', ModeSwitcherController::class)->share(ModeSwitcherController::class, [$this, 'getControllerModeSwitcherService'], \true);
        $container->share(ModeSwitcherModel::class, [$this, 'getModelModeSwitcherService'], \true);
    }

    public function getControllerModeSwitcherService(Container $container): ModeSwitcherController
    {
        return new ModeSwitcherController($container->get(ModeSwitcherModel::class), $container->get(Input::class), $container->get(AbstractApplication::class));
    }

    public function getModelModeSwitcherService(Container $container): ModeSwitcherModel
    {
        $model = new ModeSwitcherModel($container->get(Registry::class), $container->get(Cache::class), $container->get(TogglePlugins::class));
        $model->setDb($container->get(DatabaseInterface::class));

        return $model;
    }
}
