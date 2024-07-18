<?php

namespace _JchOptimizeVendor\Illuminate\Contracts\View;

interface Engine
{
    /**
     * Get the evaluated contents of the view.
     *
     * @param string $path
     *
     * @return string
     */
    public function get($path, array $data = []);
}
