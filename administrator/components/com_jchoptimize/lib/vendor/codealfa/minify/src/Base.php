<?php

/**
 * @author    Samuel Marshall <sdmarshall73@gmail.com>
 * @copyright Copyright (c) 2020 Samuel Marshall
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace CodeAlfa\Minify;

abstract class Base
{
    use \CodeAlfa\RegexTokenizer\Base;

    protected function __construct()
    {
        if (!\defined('CODEALFA_MINIFY_CONFIGURED')) {
            \ini_set('pcre.backtrack_limit', '1000000');
            \ini_set('pcre.recursion_limit', '1000000');
            \ini_set('pcre.jit', '0');
            \define('CODEALFA_MINIFY_CONFIGURED', 1);
        }
    }

    /**
     * @staticvar bool $tm
     *
     * @param mixed $regexNum
     *
     * @psalm-param callable(array<array-key, string>): string $callback
     *
     * @throws \Exception
     */
    protected function _replace(string $regex, string $replacement, string $code, $regexNum, ?callable $callback = null): string
    {
        static $tm = \false;
        if (\false === $tm) {
            $this->_debug('', '');
            $tm = \true;
        }
        if (empty($callback)) {
            $op_code = \preg_replace($regex, $replacement, $code);
        } else {
            $op_code = \preg_replace_callback($regex, $callback, $code);
        }
        $this->_debug($regex, $code, $regexNum);
        self::throwExceptionOnPregError();

        return $op_code;
    }
}
