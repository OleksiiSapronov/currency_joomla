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

namespace JchOptimize;

use _JchOptimizeVendor\Psr\Container\ContainerExceptionInterface;
use _JchOptimizeVendor\Psr\Container\ContainerInterface;
use _JchOptimizeVendor\Psr\Container\NotFoundExceptionInterface;
use Joomla\Input\Input;

\defined('_JEXEC') or exit('Restricted access');
class ControllerResolver
{
    private ContainerInterface $container;

    private Input $input;

    public function __construct(ContainerInterface $container, Input $input)
    {
        $this->container = $container;
        $this->input = $input;
    }

    public function resolve()
    {
        $controller = $this->getController();
        if ($this->container->has($controller)) {
            try {
                \call_user_func([$this->container->get($controller), 'execute']);
            } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
                throw new \InvalidArgumentException(\sprintf('Controller %s not found', $controller));
            }
        } else {
            throw new \InvalidArgumentException(\sprintf('Cannot resolve controller: %s', $controller));
        }
    }

    private function getController(): string
    {
        // @var string
        return $this->input->get('view', 'ControlPanel');
    }
}
