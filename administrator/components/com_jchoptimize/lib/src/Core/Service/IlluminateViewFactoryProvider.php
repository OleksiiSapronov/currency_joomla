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

namespace JchOptimize\Core\Service;

use _JchOptimizeVendor\Illuminate\Contracts\View\Engine;
use _JchOptimizeVendor\Illuminate\Events\Dispatcher;
use _JchOptimizeVendor\Illuminate\Filesystem\Filesystem;
use _JchOptimizeVendor\Illuminate\View\Compilers\BladeCompiler;
use _JchOptimizeVendor\Illuminate\View\Engines\CompilerEngine;
use _JchOptimizeVendor\Illuminate\View\Engines\EngineResolver;
use _JchOptimizeVendor\Illuminate\View\Factory;
use _JchOptimizeVendor\Illuminate\View\FileViewFinder;
use _JchOptimizeVendor\Joomla\DI\Container;
use _JchOptimizeVendor\Joomla\DI\ServiceProviderInterface;
use JchOptimize\Platform\Paths;
use Joomla\Filesystem\Folder;

\defined('_JCH_EXEC') or exit('Restricted access');
class IlluminateViewFactoryProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->set(Factory::class, function () {
            $templateCachePath = Paths::templateCachePath();
            // Make sure cache path exists
            if (!\file_exists($templateCachePath)) {
                // Create folder including parent folders if they don't exist
                Folder::create($templateCachePath);
            }
            $filesystem = new Filesystem();
            $resolver = new EngineResolver();
            $resolver->register('blade', static function () use ($filesystem, $templateCachePath): Engine {
                return new CompilerEngine(new BladeCompiler($filesystem, $templateCachePath));
            });

            return new Factory($resolver, new FileViewFinder($filesystem, []), new Dispatcher());
        });
    }
}
