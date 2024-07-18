<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Model;

use _JchOptimizeVendor\Joomla\DI\Container;
use _JchOptimizeVendor\Joomla\DI\ContainerAwareInterface;
use _JchOptimizeVendor\Joomla\DI\ContainerAwareTrait;
use _JchOptimizeVendor\Joomla\Model\StatefulModelInterface;
use _JchOptimizeVendor\Joomla\Model\StatefulModelTrait;
use JchOptimize\Core\PageCache\CaptureCache;
use JchOptimize\Core\PageCache\PageCache as CorePageCache;
use JchOptimize\GetApplicationTrait;
use Joomla\Registry\Registry;

\defined('_JEXEC') or exit('Restricted Access');
class PageCache implements StatefulModelInterface, ContainerAwareInterface
{
    use StatefulModelTrait;
    use ContainerAwareTrait;
    use GetApplicationTrait;

    private CorePageCache $pageCache;

    /**
     * Constructor.
     */
    public function __construct(CorePageCache $pageCache, Container $container)
    {
        $this->pageCache = $pageCache;
        $this->container = $container;
        if (JCH_PRO) {
            /** @var CaptureCache $captureCache */
            $captureCache = $this->container->get(CaptureCache::class);
            $captureCache->updateHtaccess();
        }

        try {
            $registry = $this->populateRegistryFromRequest(['filter', 'list']);
        } catch (\Exception $e) {
            $registry = new Registry();
        }
        $this->state = $registry;
    }

    public function getItems(): array
    {
        $filters = ['time-1', 'time-2', 'search', 'device', 'adapter', 'http-request'];
        foreach ($filters as $filter) {
            /** @var string $filterState */
            $filterState = $this->getState()->get("filter_{$filter}");
            if (!empty($filterState)) {
                $this->pageCache->setFilter("filter_{$filter}", $filterState);
            }
        }
        // ordering
        /** @var string $fullOrderingList */
        $fullOrderingList = $this->getState()->get('list_fullordering');
        if (!empty($fullOrderingList)) {
            $this->pageCache->setList('list_fullordering', $fullOrderingList);
        }

        return $this->pageCache->getItems();
    }

    public function delete(array $ids): bool
    {
        return $this->pageCache->deleteItemsByIds($ids);
    }

    public function deleteAll(): bool
    {
        return $this->pageCache->deleteAllItems();
    }

    public function getAdaptorName(): string
    {
        return $this->pageCache->getAdapterName();
    }

    public function isCaptureCacheEnabled(): bool
    {
        return $this->pageCache->isCaptureCacheEnabled();
    }

    /**
     * @param string[] $keys
     *
     * @throws \Exception
     *
     * @psalm-param list{'filter', 'list'} $keys
     */
    private function populateRegistryFromRequest(array $keys): Registry
    {
        $data = new Registry();
        $app = self::getApplication();
        $session = $app->getSession();
        $input = \version_compare(JVERSION, '4', 'lt') ? $app->input : $app->getInput();
        foreach ($keys as $key) {
            // Check for value from input first
            /** @psalm-var array<string, string>|null $requestKey */
            $requestKey = $input->getString($key);
            if (\is_null($requestKey)) {
                // Not found, let's see if it's saved in session
                /** @psalm-var array<string, string>|null $requestKey */
                $requestKey = $session->get($key);
            }
            // If we've got one by now let's set it in registry
            if (!\is_null($requestKey)) {
                foreach ($requestKey as $requestName => $requestValue) {
                    if (!empty($requestValue)) {
                        $data->set($key.'_'.$requestName, $requestValue);
                    }
                }
                // Set the new value in session
                $session->set($key, $requestKey);
            }
        }

        return $data;
    }
}
