<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\View;

\defined('_JEXEC') or exit;
use _JchOptimizeVendor\Joomla\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route as JRoute;
use Joomla\CMS\Toolbar\ToolbarHelper as JToolbarHelper;
use Joomla\CMS\Uri\Uri as JUri;

class ControlPanelHtml extends HtmlView
{
    public function loadResources(): void
    {
        $document = Factory::getDocument();
        $options = ['version' => JCH_VERSION];
        $document->addStyleSheet(JUri::root(\true).'/media/com_jchoptimize/core/css/admin.css', $options);
        $document->addStyleSheet(JUri::root(\true).'/media/com_jchoptimize/css/admin-joomla.css', $options);
        $document->addStyleSheet('//netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.css', $options);
        $document->addStyleSheet(JUri::root(\true).'/media/com_jchoptimize/bootstrap/css/bootstrap5-cssgrid.css', $options);
        HTMLHelper::_('jquery.framework');
        $document->addScript(JUri::root(\true).'/media/com_jchoptimize/js/platform-joomla.js', $options);
        $document->addScript(JUri::root(\true).'/media/com_jchoptimize/core/js/file_upload.js', $options);
        $javascript = 'let configure_url = \''.JRoute::_('index.php?option=com_jchoptimize&view=Configure', \false, JROUTE::TLS_IGNORE, \true).'\';';
        $document->addScriptDeclaration($javascript);
        $script = <<<'JS'

window.addEventListener('DOMContentLoaded', (event) => {
    jchPlatform.getCacheInfo();
})
JS;
        $document->addScriptDeclaration($script);
        $aOptions = ['trigger' => 'hover focus', 'placement' => 'right', 'html' => \true];
        HTMLHelper::_('bootstrap.popover', '.hasPopover', $aOptions);
        HTMLHelper::_('bootstrap.modal');
    }

    public function loadToolBar(): void
    {
        JToolbarHelper::title(Text::_(JCH_PRO ? 'COM_JCHOPTIMIZE_PRO' : 'COM_JCHOPTIMIZE'), 'dashboard');
        JToolbarHelper::preferences('com_jchoptimize');
    }
}
