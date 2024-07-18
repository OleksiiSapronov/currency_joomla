<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/joomla-platform
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die;

use JchOptimize\ContainerFactory;
use Joomla\CMS\HTML\HTMLHelper;

include_once JPATH_ADMINISTRATOR . '/components/com_jchoptimize/autoload.php';

class JFormFieldAjax extends JFormField
{
    protected $type = 'ajax';


    public function setup(SimpleXMLElement $element, $value, $group = null)
    {
        $params = ContainerFactory::getContainer()->get('params');

        if (!defined('JCH_DEBUG')) {
            define('JCH_DEBUG', ($params->get('debug', 0) && JDEBUG));
        }

        $script_options = ['framework' => false, 'relative' => true];

        HTMLHelper::_('jquery.framework', true, null, false);

        $document = JFactory::getDocument();
        $script = '';

        $options = ['version' => JCH_VERSION];
        $document->addStyleSheet(JUri::root(true) . '/media/com_jchoptimize/core/css/admin.css', $options);
        $document->addScript(JUri::root(true) . '/media/com_jchoptimize/core/js/admin-utility.js', $options);
        $document->addScript(JUri::root(true) . '/media/com_jchoptimize/js/platform-joomla.js', $options);
        $document->addScript(JUri::root(true) . '/media/com_jchoptimize/core/js/multiselect.js', $options);
        $document->addScript(JUri::root(true) . '/media/com_jchoptimize/core/js/smart-combine.js', $options);

        if (version_compare(JVERSION, '3.99.99', '>')) {
            $document->addStyleSheet(JUri::root(true) . '/media/vendor/chosen/css/chosen.css');
            $document->addStyleSheet(JUri::root(true) . '/media/com_jchoptimize/css/js-excludes-J4.css', $options);
            $document->addScript(JUri::root(true) . '/media/vendor/chosen/js/chosen.jquery.js');
            $document->addScriptDeclaration(
                'jQuery(document).ready(function() { 
	jQuery(\'.jch-multiselect\').chosen({
		width: "80%"	
	});
});'
            );
        } else {
            $document->addStyleSheet(JUri::root(true) . '/media/com_jchoptimize/css/js-excludes-J3.css', $options);
        }

        $ajax_url = JRoute::_('index.php?option=com_jchoptimize&view=Ajax', false, JRoute::TLS_IGNORE, true);

        $script .= <<<JS
var jch_observers = [];        
var jch_ajax_url = '$ajax_url';

JS;

        $document->addScriptDeclaration($script);

        return false;
    }

    protected function getInput()
    {
        return false;
    }
}
