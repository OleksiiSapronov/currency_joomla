<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

use JchOptimize\ContainerFactory;
use JchOptimize\ControllerResolver;
use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Factory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

defined('_JEXEC') or die;

require_once __DIR__ . '/autoload.php';

$app = Factory::getApplication();

if ($app->isClient('administrator') && ! $app->getIdentity()->authorise('core.manage', 'com_jchoptimize')) {
    throw new NotAllowed($app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
}

try {
    $container = ContainerFactory::getContainer();
    $container->get(ControllerResolver::class)->resolve();
} catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
    echo '<p> Error resolving controller: ' . $e->getMessage() . '</p>';
} catch (\Exception $e) {
    echo '<p> Failed initializing component: ' . $e->getMessage() . '</p>';
}
