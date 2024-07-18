<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Stdlib\StringWrapper;

interface StringWrapperInterface
{
    /**
     * Check if the given character encoding is supported by this wrapper
     * and the character encoding to convert to is also supported.
     *
     * @param string      $encoding
     * @param null|string $convertEncoding
     */
    public static function isSupported($encoding, $convertEncoding = null);

    /**
     * Get a list of supported character encodings.
     *
     * @return string[]
     */
    public static function getSupportedEncodings();

    /**
     * Set character encoding working with and convert to.
     *
     * @param string      $encoding        The character encoding to work with
     * @param null|string $convertEncoding The character encoding to convert to
     *
     * @return StringWrapperInterface
     */
    public function setEncoding($encoding, $convertEncoding = null);

    /**
     * Get the defined character encoding to work with (upper case).
     *
     * @return null|string
     */
    public function getEncoding();

    /**
     * Get the defined character encoding to convert to (upper case).
     *
     * @return null|string
     */
    public function getConvertEncoding();

    /**
     * Returns the length of the given string.
     *
     * @param string $str
     *
     * @return false|int
     */
    public function strlen($str);

    /**
     * Returns the portion of string specified by the start and length parameters.
     *
     * @param string   $str
     * @param int      $offset
     * @param null|int $length
     *
     * @return false|string
     */
    public function substr($str, $offset = 0, $length = null);

    /**
     * Find the position of the first occurrence of a substring in a string.
     *
     * @param string $haystack
     * @param string $needle
     * @param int    $offset
     *
     * @return false|int
     */
    public function strpos($haystack, $needle, $offset = 0);

    /**
     * Convert a string from defined encoding to the defined convert encoding.
     *
     * @param string $str
     * @param bool   $reverse
     *
     * @return false|string
     */
    public function convert($str, $reverse = \false);

    /**
     * Wraps a string to a given number of characters.
     *
     * @param string $str
     * @param int    $width
     * @param string $break
     * @param bool   $cut
     *
     * @return string
     */
    public function wordWrap($str, $width = 75, $break = "\n", $cut = \false);

    /**
     * Pad a string to a certain length with another string.
     *
     * @param string $input
     * @param int    $padLength
     * @param string $padString
     * @param int    $padType
     *
     * @return string
     */
    public function strPad($input, $padLength, $padString = ' ', $padType = \STR_PAD_RIGHT);
}
