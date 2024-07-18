<?php

/**
 * @see https://github.com/laminas/laminas-serializer for the canonical source repository
 */
declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Serializer\Adapter;

abstract class AbstractAdapter implements AdapterInterface
{
    /** @var AdapterOptions */
    protected $options;

    /**
     * Constructor.
     *
     * @param AdapterOptions|array|\Traversable $options
     */
    public function __construct($options = null)
    {
        if (null !== $options) {
            $this->setOptions($options);
        }
    }

    /**
     * Set adapter options.
     *
     * @param AdapterOptions|array|\Traversable $options
     *
     * @return AbstractAdapter
     */
    public function setOptions($options)
    {
        if (!$options instanceof AdapterOptions) {
            $options = new AdapterOptions($options);
        }
        $this->options = $options;

        return $this;
    }

    /**
     * Get adapter options.
     *
     * @return AdapterOptions
     */
    public function getOptions()
    {
        if (null === $this->options) {
            $this->options = new AdapterOptions();
        }

        return $this->options;
    }
}
