<?php

namespace _JchOptimizeVendor\Illuminate\View\Engines;

use _JchOptimizeVendor\Illuminate\Contracts\View\Engine;
use _JchOptimizeVendor\Illuminate\Filesystem\Filesystem;

class FileEngine implements Engine
{
    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new file engine instance.
     *
     * @param \Illuminate\Filesystem\Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
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
        return $this->files->get($path);
    }
}
