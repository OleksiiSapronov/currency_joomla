<?php
/**
 * @package         ReReplacer
 * @version         13.2.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

namespace RegularLabs\Plugin\System\ReReplacer;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Filesystem\File as JFile;
use RegularLabs\Library\File as RL_File;
use RegularLabs\Library\RegEx as RL_RegEx;

class Code
{
    public static function run($rr_string, &$rr_variables)
    {
        if ( ! is_string($rr_string) || $rr_string == '')
        {
            return '';
        }

        $rr_pre_variables = array_keys(get_defined_vars());

        ob_start();
        $rr_post_variables = self::execute($rr_string, $rr_variables);
        $rr_output         = ob_get_contents();
        ob_end_clean();

        if ( ! is_array($rr_post_variables))
        {
            return $rr_output;
        }

        $rr_diff_variables = array_diff(array_keys($rr_post_variables), $rr_pre_variables);

        foreach ($rr_diff_variables as $rr_diff_key)
        {
            if (
                in_array($rr_diff_key, ['Itemid', 'mainframe', 'app', 'document', 'doc', 'database', 'db', 'user'])
                || substr($rr_diff_key, 0, 4) == 'rr_'
            )
            {
                continue;
            }

            $rr_variables[$rr_diff_key] = $rr_post_variables[$rr_diff_key];
        }

        return $rr_output;
    }

    private static function execute($string, $rr_variables)
    {
        $function_name = 'rereplacer_php_' . md5($string);

        if (function_exists($function_name))
        {
            return $function_name($rr_variables);
        }

        $contents = self::generateFileContents($function_name, $string);

        $folder    = JFactory::getConfig()->get('tmp_path', JPATH_ROOT . '/tmp');
        $temp_file = $folder . '/' . $function_name;

        JFile::write($temp_file, $contents);

        include_once $temp_file;

        if ( ! defined('JDEBUG') || ! JDEBUG)
        {
            RL_File::delete($temp_file);
        }

        if ( ! function_exists($function_name))
        {
            // Something went wrong!
            return [];
        }

        return $function_name($rr_variables);
    }

    private static function extractUseStatements(&$string)
    {
        $use_statements = [];

        $string = trim($string);

        RL_RegEx::matchAll('^use\s+[^\s;]+\s*;', $string, $matches, 'm');

        foreach ($matches as $match)
        {
            $use_statements[] = $match[0];
            $string           = str_replace($match[0], '', $string);
        }

        $string = trim($string);

        return implode("\n", $use_statements);
    }

    private static function generateFileContents($function_name = 'rr_function', $string = '')
    {
        $use_statements = self::extractUseStatements($string);

        $init = self::getVarInits();

        $init[] =
            'if (is_array($rr_variables)) {'
            . 'foreach ($rr_variables as $rr_key => $rr_value) {'
            . '${$rr_key} = $rr_value;'
            . '}'
            . '}';

        $contents = [
            '<?php',
            'defined(\'_JEXEC\') or die;',
            $use_statements,
            'function ' . $function_name . '($rr_variables){',
            implode("\n", $init),
            $string . ';',
            'return get_defined_vars();',
            ';}',
        ];

        return implode("\n", $contents);
    }

    private static function getVarInits()
    {
        return [
            '$app = $mainframe = JFactory::getApplication();',
            '$document = $doc = JFactory::getDocument();',
            '$database = $db = JFactory::getDbo();',
            '$user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();',
            '$Itemid = $app->input->getInt(\'Itemid\');',
        ];
    }
}
