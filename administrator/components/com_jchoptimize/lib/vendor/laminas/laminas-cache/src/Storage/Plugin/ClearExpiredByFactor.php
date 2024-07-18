<?php

namespace _JchOptimizeVendor\Laminas\Cache\Storage\Plugin;

use _JchOptimizeVendor\Laminas\Cache\Storage\ClearExpiredInterface;
use _JchOptimizeVendor\Laminas\Cache\Storage\PostEvent;
use _JchOptimizeVendor\Laminas\EventManager\EventManagerInterface;

class ClearExpiredByFactor extends AbstractPlugin
{
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $callback = [$this, 'clearExpiredByFactor'];
        $this->listeners[] = $events->attach('setItem.post', $callback, $priority);
        $this->listeners[] = $events->attach('setItems.post', $callback, $priority);
        $this->listeners[] = $events->attach('addItem.post', $callback, $priority);
        $this->listeners[] = $events->attach('addItems.post', $callback, $priority);
    }

    /**
     * Clear expired items by factor after writing new item(s).
     *
     * @phpcs:disable Generic.NamingConventions.ConstructorName.OldStyle
     */
    public function clearExpiredByFactor(PostEvent $event)
    {
        $storage = $event->getStorage();
        if (!$storage instanceof ClearExpiredInterface) {
            return;
        }
        $factor = $this->getOptions()->getClearingFactor();
        if ($factor && 1 === \random_int(1, $factor)) {
            $storage->clearExpired();
        }
    }
}
