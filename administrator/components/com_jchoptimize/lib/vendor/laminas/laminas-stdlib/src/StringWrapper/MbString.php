<?php

declare(strict_types=1);

namespace _JchOptimizeVendor\Laminas\Stdlib\StringWrapper;

use _JchOptimizeVendor\Laminas\Stdlib\Exception;

class MbString extends AbstractStringWrapper
{
    /**
     * List of supported character sets (upper case).
     *
     * @see http://php.net/manual/mbstring.supported-encodings.php
     *
     * @var null|string[]
     */
    protected static $encodings;

    /**
     * Constructor.
     *
     * @throws Exception\ExtensionNotLoadedException
     */
    public function __construct()
    {
        if (!\extension_loaded('mbstring')) {
            throw new Exception\ExtensionNotLoadedException('PHP extension "mbstring" is required for this wrapper');
        }
    }

    /**
     * Get a list of supported character encodings.
     *
     * @return string[]
     */
    public static function getSupportedEncodings()
    {
        if (null === static::$encodings) {
            static::$encodings = \array_map('strtoupper', \mb_list_encodings());
            // FIXME: Converting € (UTF-8) to ISO-8859-16 gives a wrong result
            $indexIso885916 = \array_search('ISO-8859-16', static::$encodings, \true);
            if (\false !== $indexIso885916) {
                unset(static::$encodings[$indexIso885916]);
            }
        }

        return static::$encodings;
    }

    /**
     * Returns the length of the given string.
     *
     * @param string $str
     *
     * @return false|int
     */
    public function strlen($str)
    {
        return \mb_strlen($str, $this->getEncoding());
    }

    /**
     * Returns the portion of string specified by the start and length parameters.
     *
     * @param string   $str
     * @param int      $offset
     * @param null|int $length
     *
     * @return false|string
     */
    public function substr($str, $offset = 0, $length = null)
    {
        return \mb_substr($str, $offset, $length, $this->getEncoding());
    }

    /**
     * Find the position of the first occurrence of a substring in a string.
     *
     * @param string $haystack
     * @param string $needle
     * @param int    $offset
     *
     * @return false|int
     */
    public function strpos($haystack, $needle, $offset = 0)
    {
        return \mb_strpos($haystack, $needle, $offset, $this->getEncoding());
    }

    /**
     * Convert a string from defined encoding to the defined convert encoding.
     *
     * @param string $str
     * @param bool   $reverse
     *
     * @return false|string
     */
    public function convert($str, $reverse = \false)
    {
        $encoding = $this->getEncoding();
        $convertEncoding = $this->getConvertEncoding();
        if (null === $convertEncoding) {
            throw new Exception\LogicException('No convert encoding defined');
        }
        if ($encoding === $convertEncoding) {
            return $str;
        }
        $fromEncoding = $reverse ? $convertEncoding : $encoding;
        $toEncoding = $reverse ? $encoding : $convertEncoding;

        return \mb_convert_encoding($str, $toEncoding ?? '', $fromEncoding ?? '');
    }
}
