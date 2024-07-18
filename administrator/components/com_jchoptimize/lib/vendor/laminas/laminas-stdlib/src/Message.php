<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Stdlib;

class Message implements MessageInterface
{
    /** @var array */
    protected $metadata = [];

    /** @var mixed */
    protected $content = '';

    /**
     * Set message metadata.
     *
     * Non-destructive setting of message metadata; always adds to the metadata, never overwrites
     * the entire metadata container.
     *
     * @param array|int|string|\Traversable $spec
     * @param mixed                         $value
     *
     * @return Message
     *
     * @throws Exception\InvalidArgumentException
     */
    public function setMetadata($spec, $value = null)
    {
        if (\is_scalar($spec)) {
            $this->metadata[$spec] = $value;

            return $this;
        }
        if (!\is_array($spec) && !$spec instanceof \Traversable) {
            throw new Exception\InvalidArgumentException(\sprintf('Expected a string, array, or Traversable argument in first position; received "%s"', \is_object($spec) ? \get_class($spec) : \gettype($spec)));
        }
        foreach ($spec as $key => $value) {
            $this->metadata[$key] = $value;
        }

        return $this;
    }

    /**
     * Retrieve all metadata or a single metadatum as specified by key.
     *
     * @param null|int|string $key
     * @param null|mixed      $default
     *
     * @return mixed
     *
     * @throws Exception\InvalidArgumentException
     */
    public function getMetadata($key = null, $default = null)
    {
        if (null === $key) {
            return $this->metadata;
        }
        if (!\is_scalar($key)) {
            throw new Exception\InvalidArgumentException('Non-scalar argument provided for key');
        }
        if (\array_key_exists($key, $this->metadata)) {
            return $this->metadata[$key];
        }

        return $default;
    }

    /**
     * Set message content.
     *
     * @param mixed $value
     *
     * @return Message
     */
    public function setContent($value)
    {
        $this->content = $value;

        return $this;
    }

    /**
     * Get message content.
     *
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function toString()
    {
        $request = '';
        foreach ($this->getMetadata() as $key => $value) {
            $request .= \sprintf("%s: %s\r\n", (string) $key, (string) $value);
        }
        $request .= "\r\n".$this->getContent();

        return $request;
    }
}
