<?php

/**
 * @author    Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2020 Samuel Marshall
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\RegexTokenizer;

use CodeAlfa\RegexTokenizer\Debug\Debug;
use Exception;

trait Base
{
    use Debug;

    /**
     * Regex token for a string inside double quotes.
     */
    // language=RegExp
    public static function doubleQuoteStringToken(): string
    {
        return '"'.self::doubleQuoteStringValueToken().'(?:"|(?=$))';
    }

    /**
     * Regex token for the value of a string inside double quotes.
     */
    // language=RegExp
    public static function doubleQuoteStringValueToken(): string
    {
        return '(?<=")(?>(?:\\\\.)?[^\\\\"]*+)++';
    }

    /**
     * Regex token for a string enclosed by single quotes.
     */
    // language=RegExp
    public static function singleQuoteStringToken(): string
    {
        return "'".self::singleQuoteStringValueToken()."(?:'|(?=\$))";
    }

    /**
     * Regex token for the value of a string inside single quotes.
     */
    // language=RegExp
    public static function singleQuoteStringValueToken(): string
    {
        return "(?<=')(?>(?:\\\\.)?[^\\\\']*+)++";
    }

    /**
     * Regex token for a string enclosed by back ticks.
     */
    // language=RegExp
    public static function backTickStringToken(): string
    {
        return '`'.self::backTickStringValueToken().'(?:`|(?=$))';
    }

    /**
     * Regex token for the value of a string inside back ticks.
     */
    // language=RegExp
    public static function backTickStringValueToken(): string
    {
        return '(?<=`)(?>(?:\\\\.)?[^\\\\`]*+)++';
    }

    /**
     * Regex token for any string, optionally capturing the value in a capture group.
     *
     * @param bool $shouldCaptureValue Whether value should be captured in a capture group
     */
    // language=RegExp
    public static function stringWithCaptureValueToken(bool $shouldCaptureValue = \false): string
    {
        $string = '[\'"`]<<'.self::stringValueToken().'>>[\'"`]';

        return self::prepare($string, $shouldCaptureValue);
    }

    /**
     * Regex token for the value of a string regardless of which quotes are used.
     */
    // language=RegExp
    public static function stringValueToken(): string
    {
        return '(?:'.self::doubleQuoteStringValueToken().'|'.self::singleQuoteStringValueToken().'|'.self::backTickStringValueToken().')';
    }

    /**
     * Regex token for block or line comments.
     */
    // language=RegExp
    public static function commentToken(): string
    {
        return '(?:'.self::blockCommentToken().'|'.self::lineCommentToken().')';
    }

    /**
     * Regex token for block comment.
     */
    // language=RegExp
    public static function blockCommentToken(): string
    {
        return '/\\*(?>\\*?[^*]*+)*?\\*/';
    }

    /**
     * Regex token for line comment.
     */
    public static function lineCommentToken(): string
    {
        return '//[^\\r\\n]*+';
    }

    /**
     * Will throw an exception when a PHP preg error is encountered.
     *
     * @throws \Exception
     */
    protected static function throwExceptionOnPregError()
    {
        $error = \array_flip(\array_filter(\get_defined_constants(\true)['pcre'], function (string $value) {
            return '_ERROR' === \substr($value, -6);
        }, \ARRAY_FILTER_USE_KEY))[\preg_last_error()];
        if (\PREG_NO_ERROR != \preg_last_error()) {
            throw new \Exception($error);
        }
    }

    /**
     * @param string $regex              Regular expression string
     * @param bool   $shouldCaptureValue Whether value should be captured
     */
    // language=RegExp
    private static function prepare(string $regex, bool $shouldCaptureValue): string
    {
        $searchArray = ['<<<', '>>>', '<<', '>>'];
        if ($shouldCaptureValue) {
            return \str_replace($searchArray, ['(?|', ')', '(', ')'], $regex);
        }

        return \str_replace($searchArray, ['(?:', ')', '', ''], $regex);
    }
}
