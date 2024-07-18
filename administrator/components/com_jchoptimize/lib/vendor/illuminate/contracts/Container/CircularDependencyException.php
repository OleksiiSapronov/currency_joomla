<?php

namespace _JchOptimizeVendor\Illuminate\Contracts\Container;

use _JchOptimizeVendor\Psr\Container\ContainerExceptionInterface;

class CircularDependencyException extends \Exception implements ContainerExceptionInterface
{
}
