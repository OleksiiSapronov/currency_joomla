<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Stdlib;

interface MessageInterface
{
    /**
     * Set metadata.
     *
     * @param array|int|string|\Traversable $spec
     * @param mixed                         $value
     */
    public function setMetadata($spec, $value = null);

    /**
     * Get metadata.
     *
     * @param null|int|string $key
     *
     * @return mixed
     */
    public function getMetadata($key = null);

    /**
     * Set content.
     *
     * @param mixed $content
     *
     * @return mixed
     */
    public function setContent($content);

    /**
     * Get content.
     *
     * @return mixed
     */
    public function getContent();
}
