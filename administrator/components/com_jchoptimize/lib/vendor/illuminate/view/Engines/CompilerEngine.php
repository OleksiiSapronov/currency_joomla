<?php

namespace _JchOptimizeVendor\Illuminate\View\Engines;

use _JchOptimizeVendor\Illuminate\Filesystem\Filesystem;
use _JchOptimizeVendor\Illuminate\View\Compilers\CompilerInterface;
use _JchOptimizeVendor\Illuminate\View\ViewException;

use function _JchOptimizeVendor\last;

class CompilerEngine extends PhpEngine
{
    /**
     * The Blade compiler instance.
     *
     * @var \Illuminate\View\Compilers\CompilerInterface
     */
    protected $compiler;

    /**
     * A stack of the last compiled templates.
     *
     * @var array
     */
    protected $lastCompiled = [];

    /**
     * Create a new compiler engine instance.
     *
     * @param \Illuminate\View\Compilers\CompilerInterface $compiler
     * @param null|\Illuminate\Filesystem\Filesystem       $files
     */
    public function __construct(CompilerInterface $compiler, Filesystem $files = null)
    {
        parent::__construct($files ?: new Filesystem());
        $this->compiler = $compiler;
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @param string $path
     *
     * @return string
     */
    public function get($path, array $data = [])
    {
        $this->lastCompiled[] = $path;
        // If this given view has expired, which means it has simply been edited since
        // it was last compiled, we will re-compile the views so we can evaluate a
        // fresh copy of the view. We'll pass the compiler the path of the view.
        if ($this->compiler->isExpired($path)) {
            $this->compiler->compile($path);
        }
        // Once we have the path to the compiled file, we will evaluate the paths with
        // typical PHP just like any other templates. We also keep a stack of views
        // which have been rendered for right exception messages to be generated.
        $results = $this->evaluatePath($this->compiler->getCompiledPath($path), $data);
        \array_pop($this->lastCompiled);

        return $results;
    }

    /**
     * Get the compiler implementation.
     *
     * @return \Illuminate\View\Compilers\CompilerInterface
     */
    public function getCompiler()
    {
        return $this->compiler;
    }

    /**
     * Handle a view exception.
     *
     * @param int $obLevel
     *
     * @throws \Throwable
     */
    protected function handleViewException(\Throwable $e, $obLevel)
    {
        $e = new ViewException($this->getMessage($e), 0, 1, $e->getFile(), $e->getLine(), $e);
        parent::handleViewException($e, $obLevel);
    }

    /**
     * Get the exception message for an exception.
     *
     * @return string
     */
    protected function getMessage(\Throwable $e)
    {
        return $e->getMessage().' (View: '.\realpath(last($this->lastCompiled)).')';
    }
}
