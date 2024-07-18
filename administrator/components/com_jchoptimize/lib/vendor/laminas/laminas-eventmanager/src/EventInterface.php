<?php

namespace _JchOptimizeVendor\Laminas\EventManager;

/**
 * Representation of an event.
 */
interface EventInterface
{
    /**
     * Get event name.
     *
     * @return string
     */
    public function getName();

    /**
     * Get target/context from which event was triggered.
     *
     * @return null|object|string
     */
    public function getTarget();

    /**
     * Get parameters passed to the event.
     *
     * @return array|\ArrayAccess
     */
    public function getParams();

    /**
     * Get a single parameter by name.
     *
     * @param string $name
     * @param mixed  $default Default value to return if parameter does not exist
     *
     * @return mixed
     */
    public function getParam($name, $default = null);

    /**
     * Set the event name.
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Set the event target/context.
     *
     * @param null|object|string $target
     */
    public function setTarget($target);

    /**
     * Set event parameters.
     *
     * @param array|\ArrayAccess $params
     */
    public function setParams($params);

    /**
     * Set a single parameter by key.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function setParam($name, $value);

    /**
     * Indicate whether or not the parent EventManagerInterface should stop propagating events.
     *
     * @param bool $flag
     */
    public function stopPropagation($flag = \true);

    /**
     * Has this event indicated event propagation should stop?
     *
     * @return bool
     */
    public function propagationIsStopped();
}
