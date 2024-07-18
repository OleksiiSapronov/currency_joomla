<?php

namespace _JchOptimizeVendor\Laminas\EventManager;

/**
 * Representation of an event.
 *
 * Encapsulates the target context and parameters passed, and provides some
 * behavior for interacting with the event manager.
 */
class Event implements EventInterface
{
    /** @var string Event name */
    protected $name;

    /** @var object|string The event target */
    protected $target;

    /** @var array|\ArrayAccess|object The event parameters */
    protected $params = [];

    /** @var bool Whether or not to stop propagation */
    protected $stopPropagation = \false;

    /**
     * Constructor.
     *
     * Accept a target and its parameters.
     *
     * @param string             $name   Event name
     * @param object|string      $target
     * @param array|\ArrayAccess $params
     */
    public function __construct($name = null, $target = null, $params = null)
    {
        if (null !== $name) {
            $this->setName($name);
        }
        if (null !== $target) {
            $this->setTarget($target);
        }
        if (null !== $params) {
            $this->setParams($params);
        }
    }

    /**
     * Get event name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the event target.
     *
     * This may be either an object, or the name of a static method.
     *
     * @return object|string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set parameters.
     *
     * Overwrites parameters
     *
     * @param array|\ArrayAccess|object $params
     *
     * @throws Exception\InvalidArgumentException
     */
    public function setParams($params)
    {
        if (!\is_array($params) && !\is_object($params)) {
            throw new Exception\InvalidArgumentException(\sprintf('Event parameters must be an array or object; received "%s"', \gettype($params)));
        }
        $this->params = $params;
    }

    /**
     * Get all parameters.
     *
     * @return array|\ArrayAccess|object
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get an individual parameter.
     *
     * If the parameter does not exist, the $default value will be returned.
     *
     * @param int|string $name
     * @param mixed      $default
     *
     * @return mixed
     */
    public function getParam($name, $default = null)
    {
        // Check in params that are arrays or implement array access
        if (\is_array($this->params) || $this->params instanceof \ArrayAccess) {
            if (!isset($this->params[$name])) {
                return $default;
            }

            return $this->params[$name];
        }
        // Check in normal objects
        if (!isset($this->params->{$name})) {
            return $default;
        }

        return $this->params->{$name};
    }

    /**
     * Set the event name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }

    /**
     * Set the event target/context.
     *
     * @param null|object|string $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * Set an individual parameter to a value.
     *
     * @param int|string $name
     * @param mixed      $value
     */
    public function setParam($name, $value)
    {
        if (\is_array($this->params) || $this->params instanceof \ArrayAccess) {
            // Arrays or objects implementing array access
            $this->params[$name] = $value;

            return;
        }
        // Objects
        $this->params->{$name} = $value;
    }

    /**
     * Stop further event propagation.
     *
     * @param bool $flag
     */
    public function stopPropagation($flag = \true)
    {
        $this->stopPropagation = (bool) $flag;
    }

    /**
     * Is propagation stopped?
     *
     * @return bool
     */
    public function propagationIsStopped()
    {
        return $this->stopPropagation;
    }
}
