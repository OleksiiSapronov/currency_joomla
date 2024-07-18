<?php
/**
 * @package         Email Protector
 * @version         5.2.0PRO
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

defined('JPATH_PLATFORM') or die;

abstract class JHtmlEmail
{
    public static function cloak($mail, $mailto = true, $text = '', $email = true)
    {
        if ($mailto)
        {
            if ( ! $text)
            {
                $text = $mail;
            }

            $mail = '<a href="mailto:' . $mail . '">' . $text . '</a>';
        }

        return $mail;
    }
}
