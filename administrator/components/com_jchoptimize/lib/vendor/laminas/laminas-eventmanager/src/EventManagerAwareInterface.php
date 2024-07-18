<?php

namespace _JchOptimizeVendor\Laminas\EventManager;

/**
 * Interface to automate setter injection for an EventManager instance.
 */
interface EventManagerAwareInterface extends EventsCapableInterface
{
    /**
     * Inject an EventManager instance.
     */
    public function setEventManager(EventManagerInterface $eventManager);
}
