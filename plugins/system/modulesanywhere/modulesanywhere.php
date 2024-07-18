<?php
/**
 * @package         Modules Anywhere
 * @version         7.18.0PRO
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
use RegularLabs\Library\Protect as RL_Protect;
use RegularLabs\Library\SystemPlugin as RL_SystemPlugin;
use RegularLabs\Plugin\System\ModulesAnywhere\Params;
use RegularLabs\Plugin\System\ModulesAnywhere\Protect;
use RegularLabs\Plugin\System\ModulesAnywhere\Replace;

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
    JFactory::getLanguage()->load('plg_system_modulesanywhere', __DIR__);
    JFactory::getApplication()->enqueueMessage(
        JText::sprintf('MA_EXTENSION_CAN_NOT_FUNCTION', JText::_('MODULESANYWHERE'))
        . ' ' . JText::_('MA_REGULAR_LABS_LIBRARY_NOT_INSTALLED'),
        'error'
    );

    return;
}

require_once JPATH_LIBRARIES . '/regularlabs/autoload.php';

if ( ! RL_Document::isJoomlaVersion(3, 'MODULESANYWHERE'))
{
    RL_Extension::disable('modulesanywhere', 'plugin');

    RL_Document::adminError(
        JText::sprintf('RL_PLUGIN_HAS_BEEN_DISABLED', JText::_('MODULESANYWHERE'))
    );

    return;
}

if (true)
{
    class PlgSystemModulesAnywhere extends RL_SystemPlugin
    {
        public $_lang_prefix           = 'MA';
        public $_has_tags              = true;
        public $_disable_on_components = true;
        public $_jversion              = 3;

        public function processArticle(&$string, $area = 'article', $context = '', $article = null, $page = 0)
        {
            Replace::processModules($string, $area, $context, $article);
        }

        protected function changeDocumentBuffer(&$buffer)
        {
            return Replace::replaceTags($buffer, 'component');
        }

        protected function changeFinalHtmlOutput(&$html)
        {
            if (RL_Document::isFeed())
            {
                Replace::replaceTags($html);

                return true;
            }

            // only do stuff in body
            [$pre, $body, $post] = RL_Html::getBody($html);
            Replace::replaceTags($body, 'body');
            $html = $pre . $body . $post;

            return true;
        }

        protected function cleanFinalHtmlOutput(&$html)
        {
            RL_Protect::removeAreaTags($html, 'MODA');

            $params = Params::get();

            Protect::unprotectTags($html);

            RL_Protect::removeFromHtmlTagContent($html, Params::getTags(true));
            RL_Protect::removeInlineComments($html, 'Modules Anywhere');

            if ( ! $params->place_comments)
            {
                RL_Protect::removeCommentTags($html, 'Modules Anywhere');
            }
        }
    }
}
