<?php

namespace _JchOptimizeVendor\Illuminate\Support;

use _JchOptimizeVendor\Carbon\Carbon as BaseCarbon;
use _JchOptimizeVendor\Carbon\CarbonImmutable as BaseCarbonImmutable;

class Carbon extends BaseCarbon
{
    public static function setTestNow($testNow = null)
    {
        BaseCarbon::setTestNow($testNow);
        BaseCarbonImmutable::setTestNow($testNow);
    }
}
