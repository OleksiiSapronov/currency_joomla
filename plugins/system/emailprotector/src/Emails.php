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

namespace RegularLabs\Plugin\System\EmailProtector;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Html as RL_Html;
use RegularLabs\Library\RegEx as RL_RegEx;

class Emails
{
    public static function protect(&$string, $area = 'article', $context = '')
    {
        if ( ! is_string($string) || $string == '')
        {
            return false;
        }

        // No action needed if no @ is found
        if (strpos($string, '@') === false)
        {
            return false;
        }

        $check = Params::getRegex('simple');

        if ( ! RL_RegEx::match($check, $string))
        {
            return false;
        }

        // Check if tags are in the text snippet used for the search component
        if (strpos($context, 'com_search.') === 0)
        {
            $limit = explode('.', $context, 2);
            $limit = (int) array_pop($limit);

            $string_check = substr($string, 0, $limit);

            if (strpos($string_check, '@') === false || ! RL_RegEx::match($check, $string_check))
            {
                return false;
            }
        }

        self::protectEmailsInJavascript($string);

        Protect::_($string);

        self::protectEmailsInString($string);

        Protect::unprotect($string);

        return true;
    }

    /**
     * Replace @ and dots with [AT] and [DOT]
     *
     * @param string $string String containing possible emails
     */
    private static function cloakEmails($string)
    {
        if (empty($string))
        {
            return $string;
        }

        $params = Params::get();

        if ($params->spoof == 2)
        {
            return self::replaceEmailsWithText($string, JText::_($params->custom_text));
        }

        if ($params->spoof)
        {
            return self::replaceAtAndDotsInEmails($string);
        }

        return self::replaceEmailsWithSpans($string);
    }

    private static function cloakEmailsInFeeds($mailto, $text = '')
    {
        $params      = Params::get();
        $custom_text = $params->protect_in_feeds == 2 ? $params->feed_text : '';

        return self::cloakEmailsInFormat($mailto, $text, $custom_text);
    }

    private static function cloakEmailsInFormat($mailto, $text = '', $custom_text = '')
    {
        // Replace with custom text
        if ($custom_text)
        {
            return JText::_($custom_text);
        }

        // Replace with cloaked email
        if ( ! $text)
        {
            $text = $mailto;
        }

        return self::replaceAtAndDotsInEmails($text);
    }

    private static function cloakEmailsInJSON($mailto, $text = '')
    {
        $params      = Params::get();
        $custom_text = $params->protect_in_json == 2 ? $params->json_text : '';

        return self::cloakEmailsInFormat($mailto, $text, $custom_text);
    }

    private static function cloakEmailsInPDFs($mailto, $text = '')
    {
        $params      = Params::get();
        $custom_text = $params->protect_in_pdfs == 2 ? $params->pdf_text : '';

        return self::cloakEmailsInFormat($mailto, $text, $custom_text);
    }

    private static function createId()
    {
        return 'ep_' . substr(md5(rand()), 0, 8);
    }

    /**
     * Create output with comment tag and script and a link around the text
     *
     * @param string  $text Inner text.
     * @param string  $id   ID of the main span.
     * @param boolean $pre  Prepending attributes in <a> tag
     * @param boolean $post Ending attributes in <a> tag
     *
     * @return  string   The html.
     */
    private static function createLink($text, $id, $pre = '', $post = '')
    {
        return
            '<a ' . $pre . 'href="javascript:/* ' . htmlentities(JText::_('EP_MESSAGE_PROTECTED'), ENT_COMPAT, 'UTF-8') . '*/"' . $post . '>'
            . $text
            . '</a>'
            . '<script>RegularLabs.EmailProtector.unCloak("' . $id . '", true);</script>';
    }

    private static function createLinkMailto($text, $mailto, $pre = '', $post = '')
    {
        $params = Params::get();

        $id = self::createId();

        if ($text)
        {
            $text .= self::createSpans($mailto, $id, true);

            return self::createLink($text, $id, $pre, $post);
        }

        $text = self::createSpans($mailto, $id, 1);

        if ($params->spoof)
        {
            $id     = self::createId();
            $mailto = self::replaceAtAndDotsInEmails($mailto);

            $text .= self::createSpans($mailto, $id, false);
        }

        return self::createLink($text, $id, $pre, $post);
    }

    /**
     * Create output with comment tag and script
     *
     * @param string $text Inner text.
     *
     * @return  string   The html.
     */
    private static function createOutput($text)
    {
        return '<!-- ' . JText::_('EP_MESSAGE_PROTECTED') . ' -->' . $text;
    }

    /**
     * Convert text to encoded spans.
     *
     * @param string  $string Text to convert.
     * @param string  $id     ID of the main span.
     * @param boolean $hide   Hide the spans?
     *
     * @return  string   The encoded string.
     */
    private static function createSpans($string, $id = 0, $hide = false)
    {
        $split = preg_split('##u', $string, -1, PREG_SPLIT_NO_EMPTY);

        $size  = ceil(count($split) / 6);
        $parts = ['', '', '', '', '', ''];

        foreach ($split as $i => $c)
        {
            $v   = ($c == '@' || (strlen($c) === 1 && rand(0, 2))) ? '&#' . ord($c) . ';' : $c;
            $pos = (int) floor($i / $size);

            $parts[$pos] .= $v;
        }

        $parts = [
            [$parts[0], $parts[5]],
            [$parts[1], $parts[4]],
            [$parts[2], $parts[3]],
        ];

        $html = [];

        $html[] = '<span class="cloaked_email' . ($id ? ' ' . $id : '') . '"' . ($hide ? ' style="display:none;"' : '') . '>';

        foreach ($parts as $part)
        {
            $attributes = [
                'data-ep-a="' . $part[0] . '"',
                'data-ep-b="' . $part[1] . '"',
            ];
            shuffle($attributes);
            $html[] = '<span ' . implode(' ', $attributes) . '>';
        }

        $html[] = '</span></span></span></span>';

        return implode('', $html);
    }

    /**
     * Protects the email address with a series of spans
     *
     * @param string  $mailto The mailto address in the surrounding link.
     * @param string  $text   String containing possible emails
     * @param boolean $pre    Prepending attributes in <a> tag
     * @param boolean $post   Ending attributes in <a> tag
     *
     * @return  string  The cloaked email.
     */
    private static function protectEmail($mailto, $text = '', $pre = '', $post = '')
    {
        $params = Params::get();

        $id = 0;

        // In FEEDS
        if (RL_Document::isFeed())
        {
            return self::cloakEmailsInFeeds($mailto, $text);
        }

        // In PDFS
        if (RL_Document::isPDF())
        {
            return self::cloakEmailsInPDFs($mailto, $text);
        }

        // In JSON
        if (RL_Document::isJSON())
        {
            return self::cloakEmailsInJSON($mailto, $text);
        }

        // In HTML
        $text = self::cloakEmails($text);

        if ($params->mode == 1 && $text && $id && ! $mailto)
        {
            return self::createOutput(self::createLink($text, $id, $pre, $post));
        }

        if ($params->mode && $mailto)
        {
            return self::createOutput(self::createLinkMailto($text, $mailto, $pre, $post));
        }

        return self::createOutput($text);
    }

    private static function protectEmailsInJavascript(&$string)
    {
        $params = Params::get();
        $regex  = Params::getRegex('js');

        if (
            ! $params->protect_in_js
            || strpos($string, '</script>') === false
            || ! RL_RegEx::matchAll($regex, $string, $matches)
        )
        {
            return;
        }

        foreach ($matches as $match)
        {
            $script = $match[0];
            self::protectEmailsInJavascriptTag($script);

            $string = str_replace($match[0], $script, $string);
        }
    }

    private static function protectEmailsInJavascriptTag(&$string)
    {
        $regex = Params::getRegex('injs');

        while (RL_RegEx::match($regex, $string, $regs, null, PREG_OFFSET_CAPTURE))
        {
            $protected = str_replace(
                ['.', '@'],
                [
                    $regs[1][0] . ' + ' . 'String.fromCharCode(46)' . ' + ' . $regs[1][0],
                    $regs[1][0] . ' + ' . 'String.fromCharCode(64)' . ' + ' . $regs[1][0],
                ],
                $regs[0][0]
            );
            $string    = substr_replace($string, $protected, $regs[0][1], strlen($regs[0][0]));
        }
    }

    private static function protectEmailsInString(&$string)
    {
        // Do not protect if {emailprotector=off} or {emailcloak=off} is found in text
        if (
            strpos($string, '{emailprotector=off}') !== false
            || strpos($string, '{emailcloak=off}') !== false
            || strpos($string, '<!-- EPOFF -->') !== false
        )
        {
            $string = str_replace(
                [
                    '<p>{emailprotector=off}</p>', '{emailprotector=off}',
                    '<p>{emailcloak=off}</p>', '{emailcloak=off}',
                ],
                '<!-- EPOFF -->',
                $string
            );

            return;
        }

        Protect::protectHtmlCommentTags($string);

        if (strpos($string, '@') === false)
        {
            return;
        }

        $check = Params::getRegex('simple');

        if ( ! RL_RegEx::match($check, $string))
        {
            return;
        }

        [$pre_string, $string, $post_string] = RL_Html::getContentContainingSearches(
            $string,
            ['@']
        );

        // Fix derivatives of link code <a href="http://mce_host/ourdirectory/email@domain.com">email@domain.com</a>
        // This happens when inserting an email in TinyMCE, cancelling its suggestion to add the mailto: prefix...
        if (strpos($string, 'mce_host') !== false)
        {
            $string = RL_RegEx::replace('"http://mce_host([\x20-\x7f][^<>]+/)', '"mailto:', $string);
        }

        $regex = Params::getRegex('link');

        // Search for derivatives of link code <a href="mailto:email@domain.com">anytext</a>
        RL_RegEx::matchAll($regex, $string, $emails);

        if ( ! empty($emails))
        {
            foreach ($emails as $email)
            {
                $mail      = str_replace('&amp;', '&', $email['mailto']);
                $protected = self::protectEmail($mail, $email['text'], $email['link_pre'], $email['link_post']);
                $string    = substr_replace($string, $protected, strpos($string, $email[0]), strlen($email[0]));
            }
        }

        if ( ! RL_RegEx::match($check, $string))
        {
            $string = $pre_string . $string . $post_string;

            return;
        }

        Protect::protectHtmlTags($string);

        if ( ! RL_RegEx::match($check, $string))
        {
            $string = $pre_string . $string . $post_string;

            return;
        }

        $params = Params::get();
        $regex  = Params::getRegex('email');

        // Search for plain text email@domain.com
        RL_RegEx::matchAll($regex, $string, $emails);

        if ( ! empty($emails))
        {
            foreach ($emails as $email)
            {
                $protected = self::protectEmail($params->mode == 1 ? $email['email'] : '', $email['email']);
                $string    = substr_replace($string, $protected, strpos($string, $email[0]), strlen($email[0]));
            }
        }

        $string = $pre_string . $string . $post_string;
    }

    /**
     * Replace @ and dots with [AT] and [DOT]
     *
     * @param string $string String containing possible emails
     */
    private static function replaceAtAndDotsInEmails($string)
    {
        $regex = Params::getRegex('email');

        while (RL_RegEx::match($regex, $string, $match, null, PREG_OFFSET_CAPTURE))
        {
            $replace = ['<small> ' . JText::_('EP_AT') . ' </small>', '<small> ' . JText::_('EP_DOT') . ' </small>'];

            if (RL_Document::isFeed() || RL_Document::isPDF())
            {
                $replace = [' ' . JText::_('EP_AT') . ' ', ' ' . JText::_('EP_DOT') . ' '];
            }

            $email  = str_replace(['@', '.'], $replace, $match['email'][0]);
            $string = substr_replace($string, $email, $match['email'][1], strlen($match['email'][0]));
        }

        return $string;
    }

    /**
     * Replace @ and dots with [AT] and [DOT]
     *
     * @param string $string String containing possible emails
     */
    private static function replaceEmailsWithSpans($string)
    {
        $regex = Params::getRegex('email');

        while (RL_RegEx::match($regex, $string, $match, null, PREG_OFFSET_CAPTURE))
        {
            $id        = self::createId();
            $protected = self::createSpans($match['email'][0], $id);
            $string    = substr_replace($string, $protected, $match['email'][1], strlen($match['email'][0]))
                . '<script>RegularLabs.EmailProtector.unCloak("' . $id . '");</script>';
        }

        return $string;
    }

    /**
     * Replace email addresses with custom text
     *
     * @param string $string String containing possible emails
     * @param string $text   Text to place instead of the email
     */
    private static function replaceEmailsWithText($string, $text)
    {
        $regex = Params::getRegex('email');

        return RL_RegEx::replace($regex, $text, $string);
    }
}
