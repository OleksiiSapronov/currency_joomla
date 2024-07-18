@php

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

    use JchOptimize\Core\Admin\Icons;
    use JchOptimize\Platform\Utility;
    use Joomla\CMS\Router\Route as JRoute;
    use Joomla\CMS\Language\Text;

    defined( '_JEXEC' ) or die( 'Restricted Access' );

    $page = JRoute::_( 'index.php?option=com_jchoptimize&view=OptimizeImage&task=optimizeimage', false, JRoute::TLS_IGNORE, true );

    $aAutoOptimize = [
        [
            'link'    => '',
            'icon'    => 'auto_optimize.png',
            'name'    => Utility::translate( 'Optimize Images' ),
            'script'  => 'onclick="jchIOptimizeApi.optimizeImages(\'' . $page . '\', \'auto\'); return false;"',
            'id'      => 'auto-optimize-images',
            'class'   => '',
            'proonly' => true
        ]
    ];

    $aManualOptimize = [
        [
            'link'    => '',
            'icon'    => 'manual_optimize.png',
            'name'    => Utility::translate( 'Optimize Images' ),
            'script'  => 'onclick="jchIOptimizeApi.optimizeImages(\'' . $page . '\', \'manual\'); return false;"',
            'id'      => 'manual-optimize-images',
            'class'   => '',
            'proonly' => true
        ]
    ];
    /** @var Icons $icons */
use function _JchOptimizeVendor\e;

@endphp

@if (version_compare(JVERSION, '3.999.999', 'le'))
    @include('navigation')
@endif

<div class="grid mt-3">
    <div class="g-col-12 g-col-lg-6">
        <div id="api2-utilities-block" class="admin-panel-block">
            <h4>{{ Text::_('COM_JCHOPTIMIZE_API2_UTILITY_SETTING') }}</h4>
            <p class="alert alert-info">{{ Text::_('COM_JCHOPTIMIZE_API2_UTILITY_SETTING_DESC') }}</p>
            <div class="icons-container">
                {!! $icons->printIconsHTML($icons->compileUtilityIcons($icons->getApi2utilityArray())) !!}
            </div>
        </div>
    </div>
    <div class="g-col-12 g-col-lg-6">
        <div id="auto-optimize-block" class="admin-panel-block">
            <h4>{{ Text::_('COM_JCHOPTIMIZE_OPTIMIZE_IMAGES_BY_URLS') }}</h4>
            <p class="alert alert-info">{{ Text::_('COM_JCHOPTIMIZE_OPTIMIZE_IMAGES_BY_URLS_DESC') }}</p>
            <div class="icons-container">
                {!! $icons->printIconsHTML($aAutoOptimize) !!}
            </div>
        </div>
    </div>
    <div class="g-col-12">
        <div id="manual-optimize-block" class="admin-panel-block">
            <div id="optimize-images-container" class="">
                <h4>{{ Text::_('COM_JCHOPTIMIZE_OPTIMIZE_IMAGES_BY_FOLDER') }}</h4>
                <p class="alert alert-info">{{ Text::_('COM_JCHOPTIMIZE_OPTIMIZE_IMAGES_BY_FOLDER_DESC') }}</p>
                <div class="grid">
                    <div class="g-col-12 g-col-lg-3 g-col-xl-4">
                        <div id="file-tree-container" class=""></div>
                    </div>
                    <div class="g-col-12 g-col-lg-6 g-col-xl-6">
                        <div id="files-container" class=""></div>
                    </div>
                    <div class="g-col-12 g-col-lg-3 g-col-xl-2">
                        <div class="icons-container">
                            <div class="">{!! $icons->printIconsHTML($aManualOptimize) !!}</div>
                        </div>
                    </div>
                    <div style="clear:both"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@if(version_compare(JVERSION, '4', 'ge'))
    <div id="optimize-images-modal-container" class="modal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Optimizing Images</h5>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>
@else
    <div id="optimize-images-modal-container" class="modal hide fade" role="dialog" aria-labelledby="optimizeImageModalContainerLabel" tabindex="-1"
         aria-hidden="true">
        <div class="modal-header">
            <h5 class="modal-title">Optimizing Images</h5>
        </div>
        <div class="modal-body">
        </div>
    </div>
@endif
