<?php

/**
 * @see https://github.com/laminas/laminas-serializer for the canonical source repository
 */
declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Serializer\Adapter;

use _JchOptimizeVendor\Laminas\Json\Json as LaminasJson;
use _JchOptimizeVendor\Laminas\Serializer\Exception;

class JsonOptions extends AdapterOptions
{
    /** @var bool */
    protected $cycleCheck = \false;

    /** @var bool */
    protected $enableJsonExprFinder = \false;

    /** @var int */
    protected $objectDecodeType = LaminasJson::TYPE_ARRAY;

    /**
     * @param bool $flag
     *
     * @return JsonOptions
     */
    public function setCycleCheck($flag)
    {
        $this->cycleCheck = (bool) $flag;

        return $this;
    }

    /**
     * @return bool
     */
    public function getCycleCheck()
    {
        return $this->cycleCheck;
    }

    /**
     * @param bool $flag
     *
     * @return JsonOptions
     */
    public function setEnableJsonExprFinder($flag)
    {
        $this->enableJsonExprFinder = (bool) $flag;

        return $this;
    }

    /**
     * @return bool
     */
    public function getEnableJsonExprFinder()
    {
        return $this->enableJsonExprFinder;
    }

    /**
     * @param int $type
     *
     * @return JsonOptions
     *
     * @throws Exception\InvalidArgumentException
     */
    public function setObjectDecodeType($type)
    {
        $type = (int) $type;
        if (LaminasJson::TYPE_ARRAY !== $type && LaminasJson::TYPE_OBJECT !== $type) {
            throw new Exception\InvalidArgumentException('Unknown decode type: '.$type);
        }
        $this->objectDecodeType = $type;

        return $this;
    }

    /**
     * @return int
     */
    public function getObjectDecodeType()
    {
        return $this->objectDecodeType;
    }
}
