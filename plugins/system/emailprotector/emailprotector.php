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

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Extension as RL_Extension;
use RegularLabs\Library\Html as RL_Html;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\SystemPlugin as RL_SystemPlugin;
use RegularLabs\Plugin\System\EmailProtector\Document;
use RegularLabs\Plugin\System\EmailProtector\Emails;
use RegularLabs\Plugin\System\EmailProtector\Params;
use RegularLabs\Plugin\System\EmailProtector\Protect;

// Do not instantiate plugin on install pages
// to prevent installation/update breaking because of potential breaking changes
$input = JFactory::getApplication()->input;
if (in_array($input->get('option'), ['com_installer', 'com_regularlabsmanager']) && $input->get('action') != '')
{
    return;
}

if ( ! is_file(__DIR__ . '/vendor/autoload.php'))
{
    return;
}

require_once __DIR__ . '/vendor/autoload.php';

if ( ! is_file(JPATH_LIBRARIES . '/regularlabs/autoload.php')
    || ! is_file(JPATH_LIBRARIES . '/regularlabs/src/SystemPlugin.php')
)
{
    JFactory::getLanguage()->load('plg_system_emailprotector', __DIR__);
    JFactory::getApplication()->enqueueMessage(
        JText::sprintf('EP_EXTENSION_CAN_NOT_FUNCTION', JText::_('EMAILPROTECTOR'))
        . ' ' . JText::_('EP_REGULAR_LABS_LIBRARY_NOT_INSTALLED'),
        'error'
    );

    return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3, 'EMAILPROTECTOR'))
{
    RL_Extension::disable('emailprotector', 'plugin');

    RL_Document::adminError(
        JText::sprintf('RL_PLUGIN_HAS_BEEN_DISABLED', JText::_('EMAILPROTECTOR'))
    );

    return;
}

if (JFactory::getApplication()->isClient('site'))
{
    // Include the custom JHtmlEmail class
    $classes = get_declared_classes();

    if ( ! in_array('JHtmlEmail', $classes) && ! in_array('jhtmlemail', $classes))
    {
        require_once __DIR__ . '/jhtmlemail.php';
    }
}

if (true)
{
    class PlgSystemEmailProtector extends RL_SystemPlugin
    {
        public $_lang_prefix        = 'EP';
        public $_page_types         = ['html', 'feed', 'pdf'];
        public $_can_disable_by_url = false;
        public $_jversion           = 3;

        protected function passChecks()
        {
            // Disable on vCards
            if (JFactory::getApplication()->input->get('format') == 'vcf')
            {
                return false;
            }

            $params = Params::get();

            if ($this->_doc_ready && ! $params->protect_in_feeds && RL_Document::isFeed())
            {
                return false;
            }

            if ($this->_doc_ready && ! $params->protect_in_pdfs && RL_Document::isPDF())
            {
                return false;
            }

            return parent::passChecks();
        }

        public function processArticle(&$string, $area = 'article', $context = '', $article = null, $page = 0)
        {
            // Do not protect email when generating output for custom fields, do that at a later stage (changeDocumentBuffer / changeFinalHtmlOutput)
            if (
                strpos($context, '_k2') !== false
                && strpos($context, '-extrafields') !== false
            )
            {
                return;
            }

            Emails::protect($string, $area, $context);
        }

        protected function loadStylesAndScripts(&$buffer)
        {
            Document::loadStylesAndScripts($buffer);
        }

        protected function changeDocumentBuffer(&$buffer)
        {
            Emails::protect($buffer, 'component');

            return true;
        }

        protected function changeFinalHtmlOutput(&$html)
        {
            // only do stuff in body
            [$pre, $body, $post] = RL_Html::getBody($html);
            Emails::protect($body);
            $html = $pre . $body . $post;

            if ( ! RL_Document::isHtml())
            {
                return true;
            }

            if (strpos($html, 'RegularLabs.EmailProtector.unCloak') === false)
            {
                // remove style and script if no emails are found
                RL_Document::removeScriptsStyles($html, 'Email Protector');

                return true;
            }

            $params = Params::get();

            // replace id placeholders with random ids
            $html = RL_RegEx::replace(
                'data-ep-a([^0-9a-z])',
                'data-ep-a' . $params->id_pre . '\1',
                $html
            );
            $html = RL_RegEx::replace(
                'data-ep-b([^0-9a-z])',
                'data-ep-b' . $params->id_post . '\1',
                $html
            );

            Protect::removeInlineComments($html);

            return true;
        }

        protected function cleanFinalHtmlOutput(&$html)
        {
            $html = str_replace('<!-- EPOFF -->', '', $html);
        }
    }
}
