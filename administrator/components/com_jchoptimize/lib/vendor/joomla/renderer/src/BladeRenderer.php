<?php

/**
 * Part of the Joomla Framework Renderer Package.
 *
 * @copyright  Copyright (C) 2005 - 2021 Open Source Matters, Inc. All rights reserved.
 * @license    http://www.gnu.org/licenses/lgpl-2.1.txt GNU Lesser General Public License Version 2.1 or Later
 */

namespace _JchOptimizeVendor\Joomla\Renderer;

use _JchOptimizeVendor\Illuminate\Contracts\View\Engine;
use _JchOptimizeVendor\Illuminate\Events\Dispatcher;
use _JchOptimizeVendor\Illuminate\Filesystem\Filesystem;
use _JchOptimizeVendor\Illuminate\View\Compilers\BladeCompiler;
use _JchOptimizeVendor\Illuminate\View\Engines\CompilerEngine;
use _JchOptimizeVendor\Illuminate\View\Engines\EngineResolver;
use _JchOptimizeVendor\Illuminate\View\Factory;
use _JchOptimizeVendor\Illuminate\View\FileViewFinder;

/**
 * Blade class for rendering output.
 *
 * @since  2.0.0
 */
class BladeRenderer extends AbstractRenderer implements AddTemplateFolderInterface
{
    /**
     * Rendering engine.
     *
     * @var Factory
     *
     * @since  2.0.0
     */
    private $renderer;

    /**
     * Constructor.
     *
     * @param Factory $renderer Rendering engine
     *
     * @since   2.0.0
     */
    public function __construct(Factory $renderer = null)
    {
        if (!$renderer) {
            $filesystem = new Filesystem();
            $resolver = new EngineResolver();
            $resolver->register('blade', static function () use ($filesystem): Engine {
                return new CompilerEngine(new BladeCompiler($filesystem));
            });
            $renderer = new Factory($resolver, new FileViewFinder($filesystem, []), new Dispatcher());
        }
        $this->renderer = $renderer;
    }

    /**
     * Add a folder with alias to the renderer.
     *
     * @param string $directory The folder path
     * @param string $alias     The folder alias (unused)
     *
     * @return $this
     *
     * @since   2.0.0
     */
    public function addFolder(string $directory, string $alias = '')
    {
        $this->getRenderer()->addLocation($directory);

        return $this;
    }

    /**
     * Get the rendering engine.
     *
     * @return Factory
     *
     * @since   2.0.0
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Checks if folder, folder alias, template or template path exists.
     *
     * @param string $path Full path or part of a path
     *
     * @return bool True if the path exists
     *
     * @since   2.0.0
     */
    public function pathExists(string $path): bool
    {
        return $this->getRenderer()->exists($path);
    }

    /**
     * Render and return compiled data.
     *
     * @param string $template The template file name
     * @param array  $data     The data to pass to the template
     *
     * @return string Compiled data
     *
     * @since   2.0.0
     */
    public function render(string $template, array $data = []): string
    {
        $data = \array_merge($this->data, $data);

        return $this->getRenderer()->make($template, $data)->render();
    }
}
