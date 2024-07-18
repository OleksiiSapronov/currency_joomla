<?php

namespace _JchOptimizeVendor\Illuminate\Container;

/**
 * @internal
 */
class Util
{
    /**
     * If the given value is not an array and not null, wrap it in one.
     *
     * From Arr::wrap() in Illuminate\Support.
     *
     * @param mixed $value
     *
     * @return array
     */
    public static function arrayWrap($value)
    {
        if (\is_null($value)) {
            return [];
        }

        return \is_array($value) ? $value : [$value];
    }

    /**
     * Return the default value of the given value.
     *
     * From global value() helper in Illuminate\Support.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public static function unwrapIfClosure($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }

    /**
     * Get the class name of the given parameter's type, if possible.
     *
     * From Reflector::getParameterClassName() in Illuminate\Support.
     *
     * @param \ReflectionParameter $parameter
     *
     * @return null|string
     */
    public static function getParameterClassName($parameter)
    {
        $type = $parameter->getType();
        if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
            return null;
        }
        $name = $type->getName();
        if (!\is_null($class = $parameter->getDeclaringClass())) {
            if ('self' === $name) {
                return $class->getName();
            }
            if ('parent' === $name && ($parent = $class->getParentClass())) {
                return $parent->getName();
            }
        }

        return $name;
    }
}
