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

namespace JchOptimize\Helper;

use JchOptimize\Platform\Utility;
use Joomla\CMS\Factory;

\defined('_JEXEC') or exit('Restricted Access');
class OptimizeImage
{
    /**
     * @param string $apiParams Json encoded string of params
     */
    public static function loadResources(string $apiParams): void
    {
        $document = Factory::getDocument();
        $options = ['version' => JCH_VERSION];
        $document->addStyleSheet(\JUri::root(\true).'/media/com_jchoptimize/jquery-ui/jquery-ui.css', $options);
        $document->addScript(\JUri::root(\true).'/media/com_jchoptimize/core/js/ioptimize-api.js', $options);
        $document->addScript(\JUri::root(\true).'/media/com_jchoptimize/jquery-ui/jquery-ui.js', $options);
        $message = \addslashes(Utility::translate('Please select files or subfolders to optimize.'));
        $noproid = \addslashes(Utility::translate('Please enter your Download ID in the component options section.'));
        $sJs = <<<JS
var jch_message = '{$message}';   
var jch_noproid = '{$noproid}';        
var jch_params = JSON.parse('{$apiParams}');
JS;
        $document->addScriptDeclaration($sJs);
    }
}
