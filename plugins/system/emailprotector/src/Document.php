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

use Joomla\CMS\Factory as JFactory;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Protect as RL_Protect;

class Document
{
    public static function loadStylesAndScripts(&$html)
    {
        // only load scripts/styles on html pages
        if ( ! RL_Document::isHtml() || RL_Document::isPDF() || RL_Document::isFeed())
        {
            return;
        }

        // Only load inline styles on print pages
        if ($html && JFactory::getApplication()->input->getInt('print', 0))
        {
            self::addInlineStyles($html);

            return;
        }

        // Add scripts/styles inline if using a special tmpl (sub-template)
        if ($html && JFactory::getApplication()->input->get('tmpl', 'index') != 'index')
        {
            self::addInlineScripts($html);
            self::addInlineStyles($html);

            return;
        }

        RL_Document::styleDeclaration(self::getCss(), 'Email Protector');
        RL_Document::scriptDeclaration(self::getJs(), 'Email Protector');
    }

    private static function addInlineScripts(&$html)
    {
        $script = RL_Document::minify(self::getJs());
        $script = RL_Protect::wrapStyleDeclaration($script, 'Email Protector');
        $script = '<script>' . $script . '</script>';

        $html = $script . $html;
    }

    private static function addInlineStyles(&$html)
    {
        $style = RL_Document::minify(self::getCss());
        $style = RL_Protect::wrapStyleDeclaration($style, 'Email Protector');
        $style = '<style>' . $style . '</style>';

        $html = $style . $html;
    }

    private static function getCss()
    {
        return '
            .cloaked_email span:before {
                content: attr(data-ep-a);
            }
            .cloaked_email span:after {
                content: attr(data-ep-b);
            }
        ';
    }

    private static function getJs()
    {
        /**
         *  Below javascript is minified via https://closure-compiler.appspot.com/home
         */
//        return '
//            window.RegularLabs = window.RegularLabs || {};
//
//            window.RegularLabs.EmailProtector = window.RegularLabs.EmailProtector || {
//                unCloak: function(classname, link) {
//                    document.querySelectorAll("." + classname).forEach((el) => {
//                        let pre      = "";
//                        let post     = "";
//                        el.className = el.className.replace(" " + classname, "");
//
//                        el.querySelectorAll("span").forEach((span) => {
//                            for (name in span.dataset) {
//                                if (name.indexOf("epA") === 0) {
//                                    pre += span.dataset[name];
//                                }
//                                if (name.indexOf("epB") === 0) {
//                                    post = span.dataset[name] + post;
//                                }
//                            }
//                        });
//
//                        if ( ! post) {
//                            return;
//                        }
//
//                        // get next html tag
//                        const script_tag = el.nextElementSibling;
//                        if (script_tag && script_tag.tagName.toLowerCase() === "script") {
//                            script_tag.parentNode.removeChild(script_tag);
//                        }
//
//                        const email = pre + post;
//
//                        if ( ! link) {
//                            el.innerHTML = email;
//
//                            return;
//                        }
//
//                        el.parentNode.href = "mailto:" + email;
//                        el.parentNode.removeChild(el);
//                    });
//                }
//            }
//            ';

        return 'window.RegularLabs=window.RegularLabs||{};window.RegularLabs.EmailProtector=window.RegularLabs.EmailProtector||{unCloak:function(e,g){document.querySelectorAll("."+e).forEach(function(a){var f="",c="";a.className=a.className.replace(" "+e,"");a.querySelectorAll("span").forEach(function(d){for(name in d.dataset)0===name.indexOf("epA")&&(f+=d.dataset[name]),0===name.indexOf("epB")&&(c=d.dataset[name]+c)});if(c){var b=a.nextElementSibling;b&&"script"===b.tagName.toLowerCase()&&b.parentNode.removeChild(b);b=f+c;g?(a.parentNode.href="mailto:"+b,a.parentNode.removeChild(a)):a.innerHTML=b}})}};';
    }
}
