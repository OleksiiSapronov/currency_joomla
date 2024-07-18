<?php

// phpcs:disable WebimpressCodingStandard.NamingConventions.AbstractClass.Prefix
declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Stdlib;

use _JchOptimizeVendor\Laminas\Stdlib\StringWrapper\Iconv;
use _JchOptimizeVendor\Laminas\Stdlib\StringWrapper\Intl;
use _JchOptimizeVendor\Laminas\Stdlib\StringWrapper\MbString;
use _JchOptimizeVendor\Laminas\Stdlib\StringWrapper\Native;
use _JchOptimizeVendor\Laminas\Stdlib\StringWrapper\StringWrapperInterface;

/**
 * Utility class for handling strings of different character encodings
 * using available PHP extensions.
 *
 * Declared abstract, as we have no need for instantiation.
 */
abstract class StringUtils
{
    /**
     * Ordered list of registered string wrapper instances.
     *
     * @var null|list<class-string<StringWrapperInterface>>
     */
    protected static $wrapperRegistry;

    /**
     * A list of known single-byte character encodings (upper-case).
     *
     * @var string[]
     */
    protected static $singleByteEncodings = ['ASCII', '7BIT', '8BIT', 'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4', 'ISO-8859-5', 'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8', 'ISO-8859-9', 'ISO-8859-10', 'ISO-8859-11', 'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15', 'ISO-8859-16', 'CP-1251', 'CP-1252'];

    /**
     * Is PCRE compiled with Unicode support?
     *
     * @var bool
     */
    protected static $hasPcreUnicodeSupport;

    /**
     * Get registered wrapper classes.
     *
     * @return string[]
     *
     * @psalm-return list<class-string<StringWrapperInterface>>
     */
    public static function getRegisteredWrappers()
    {
        if (null === static::$wrapperRegistry) {
            static::$wrapperRegistry = [];
            if (\extension_loaded('intl')) {
                static::$wrapperRegistry[] = Intl::class;
            }
            if (\extension_loaded('mbstring')) {
                static::$wrapperRegistry[] = MbString::class;
            }
            if (\extension_loaded('iconv')) {
                static::$wrapperRegistry[] = Iconv::class;
            }
            static::$wrapperRegistry[] = Native::class;
        }

        return static::$wrapperRegistry;
    }

    /**
     * Register a string wrapper class.
     *
     * @param class-string<StringWrapperInterface> $wrapper
     */
    public static function registerWrapper($wrapper)
    {
        $wrapper = (string) $wrapper;
        // using getRegisteredWrappers() here to ensure that the list is initialized
        if (!\in_array($wrapper, static::getRegisteredWrappers(), \true)) {
            static::$wrapperRegistry[] = $wrapper;
        }
    }

    /**
     * Unregister a string wrapper class.
     *
     * @param class-string<StringWrapperInterface> $wrapper
     */
    public static function unregisterWrapper($wrapper)
    {
        // using getRegisteredWrappers() here to ensure that the list is initialized
        $index = \array_search((string) $wrapper, static::getRegisteredWrappers(), \true);
        if (\false !== $index) {
            unset(static::$wrapperRegistry[$index]);
        }
    }

    /**
     * Reset all registered wrappers so the default wrappers will be used.
     */
    public static function resetRegisteredWrappers()
    {
        static::$wrapperRegistry = null;
    }

    /**
     * Get the first string wrapper supporting the given character encoding
     * and supports to convert into the given convert encoding.
     *
     * @param string      $encoding        Character encoding to support
     * @param null|string $convertEncoding OPTIONAL character encoding to convert in
     *
     * @return StringWrapperInterface
     *
     * @throws Exception\RuntimeException if no wrapper supports given character encodings
     */
    public static function getWrapper($encoding = 'UTF-8', $convertEncoding = null)
    {
        foreach (static::getRegisteredWrappers() as $wrapperClass) {
            if ($wrapperClass::isSupported($encoding, $convertEncoding)) {
                $wrapper = new $wrapperClass($encoding, $convertEncoding);
                $wrapper->setEncoding($encoding, $convertEncoding);

                return $wrapper;
            }
        }

        throw new Exception\RuntimeException('No wrapper found supporting "'.$encoding.'"'.(null !== $convertEncoding ? ' and "'.$convertEncoding.'"' : ''));
    }

    /**
     * Get a list of all known single-byte character encodings.
     *
     * @return string[]
     */
    public static function getSingleByteEncodings()
    {
        return static::$singleByteEncodings;
    }

    /**
     * Check if a given encoding is a known single-byte character encoding.
     *
     * @param string $encoding
     *
     * @return bool
     */
    public static function isSingleByteEncoding($encoding)
    {
        return \in_array(\strtoupper($encoding), static::$singleByteEncodings);
    }

    /**
     * Check if a given string is valid UTF-8 encoded.
     *
     * @param string $str
     *
     * @return bool
     */
    public static function isValidUtf8($str)
    {
        return \is_string($str) && ('' === $str || 1 === \preg_match('/^./su', $str));
    }

    /**
     * Is PCRE compiled with Unicode support?
     *
     * @return bool
     */
    public static function hasPcreUnicodeSupport()
    {
        if (null === static::$hasPcreUnicodeSupport) {
            ErrorHandler::start();
            static::$hasPcreUnicodeSupport = \defined('PREG_BAD_UTF8_OFFSET_ERROR') && 1 === \preg_match('/\\pL/u', 'a');
            ErrorHandler::stop();
        }

        return static::$hasPcreUnicodeSupport;
    }
}
