<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Stdlib\ArrayUtils;

final class MergeReplaceKey implements MergeReplaceKeyInterface
{
    /** @var mixed */
    private $data;

    /**
     * @param mixed $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
