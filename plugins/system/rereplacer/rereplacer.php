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

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Article as RL_Article;
use RegularLabs\Library\Document as RL_Document;
use RegularLabs\Library\Extension as RL_Extension;
use RegularLabs\Library\Protect as RL_Protect;
use RegularLabs\Library\RegEx as RL_RegEx;
use RegularLabs\Library\SystemPlugin as RL_SystemPlugin;
use RegularLabs\Plugin\System\ReReplacer\Items;
use RegularLabs\Plugin\System\ReReplacer\Protect;
use RegularLabs\Plugin\System\ReReplacer\Replace;
use RegularLabs\Plugin\System\ReReplacer\Tag;

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
    JFactory::getLanguage()->load('plg_system_rereplacer', __DIR__);
    JFactory::getApplication()->enqueueMessage(
        JText::sprintf('RR_EXTENSION_CAN_NOT_FUNCTION', JText::_('REREPLACER'))
        . ' ' . JText::_('RR_REGULAR_LABS_LIBRARY_NOT_INSTALLED'),
        'error'
    );

    return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3, 'REREPLACER'))
{
    RL_Extension::disable('rereplacer', 'plugin');

    RL_Document::adminError(
        JText::sprintf('RL_PLUGIN_HAS_BEEN_DISABLED', JText::_('REREPLACER'))
    );

    return;
}

if (true)
{
    class PlgSystemReReplacer extends RL_SystemPlugin
    {
        public $_lang_prefix        = 'RR';
        public $_page_types         = ['html', 'feed', 'pdf', 'ajax', 'json', 'raw'];
        public $_enable_in_admin    = true;
        public $_can_disable_by_url = false;
        public $_jversion           = 3;

        protected function extraChecks()
        {
            // return if component is not installed
            if ( ! file_exists(JPATH_ADMINISTRATOR . '/components/com_rereplacer/models/list.php'))
            {
                return false;
            }

            // don't allow ReReplacer if current page is the ReReplacer administrator page
            if (JFactory::getApplication()->input->get('option') == 'com_rereplacer')
            {
                return false;
            }

            return parent::extraChecks();
        }

        protected function handleOnContentPrepare($area, $context, &$article, &$params, $page = 0)
        {
            $items = Items::getItemList('articles');
            Items::filterItemList($items, $article);

            foreach ($items as $item)
            {
                if ( ! $item->enable_in_category && ! isset($article->catid))
                {
                    continue;
                }

                $ignore = [];

                if ( ! $item->enable_in_title)
                {
                    $ignore[] = 'title';
                }

                if ( ! $item->enable_in_author)
                {
                    $ignore[] = 'created_by_alias';
                }

                RL_Article::process($article, $context, $this, 'replace', [$item, $article], $ignore);
            }

            return false;
        }

        public function replace(&$string, $item, $article)
        {
            Replace::replace($string, $item, $article);
        }

        protected function changeDocumentBuffer(&$buffer)
        {
            // only in html
            if ( ! RL_Document::isHtml())
            {
                return false;
            }

            return Tag::tagArea($buffer, 'component');
        }

        protected function changeFinalHtmlOutput(&$html)
        {
            Replace::replaceInAreas($html);

            return true;
        }

        protected function cleanFinalHtmlOutput(&$html)
        {
            $html = RL_RegEx::replace('<\!-- (START|END): RR_[^>]* -->', '', $html);

            // Remove any leftover protection strings (shouldn't be necessary, but just in case)
            Protect::cleanProtect($html);

            // Remove any leftover protection tags
            if (strpos($html, '{noreplace}') !== false)
            {
                $item         = null;
                $string_array = Protect::stringToProtectedArray($html, $item, true);

                RL_Protect::replaceInArray($string_array, '#\{noreplace\}#', '');
                RL_Protect::replaceInArray($string_array, '#\{/noreplace\}#', '');

                $html = implode('', $string_array);
            }
        }
    }
}
