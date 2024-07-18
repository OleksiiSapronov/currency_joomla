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

    use Joomla\CMS\Language\Text;
    use Joomla\CMS\HTML\HTMLHelper;

    defined( '_JEXEC' ) or die( 'Restricted Access' );

    HTMLHelper::_('bootstrap.popover');

use function _JchOptimizeVendor\e;

@endphp

@section('table_class', 'table table-striped table-hover')

@section('browse-filters')
    <div class="js-stools clearfix">
        <div class="clearfix">
            <div class="js-stools-container-bar">

                <label for="filter_search" class="element-invisible">
                    Search </label>
                <div class="btn-wrapper input-append">
                    {!! $searchInput !!}
                    <button type="submit" class="btn hasTooltip" title="" aria-label="Search"
                            data-original-title="Search">
                        <span class="icon-search" aria-hidden="true"></span>
                    </button>
                </div>
                <div class="btn-wrapper hidden-phone">
                    <button type="button" class="btn hasTooltip js-stools-btn-filter js-stools-btn-filter" title=""
                            data-original-title="Filter the list items.">
                        Search Tools <span class="caret"></span>
                    </button>
                </div>
                <div class="btn-wrapper">
                    <button type="button" id="filter-search-clear-button" class="btn hasTooltip js-stools-btn-clear"
                            title=""
                            data-original-title="Clear">
                        Clear
                    </button>
                </div>
            </div>
            <div class="js-stools-container-list hidden-phone hidden-tablet shown" style="">
                <div class="ordering-select hidden-phone">
                    <div class="js-stools-field-list">
                        {!! $orderingSelectList !!}
                    </div>
                    <div class="js-stools-field-list">
                        {!! $limitList !!}
                    </div>
                </div>
            </div>
            <div class="pull-right" style="padding: 5px; margin-right:5px;">
                <i>Storage: <span class="badge badge-info">{{$adapter}}</span></i>
                <i class="ms-1">Http Request:
                    @if($httpRequest)
                        <span class="badge badge-success">On</span>
                    @else
                        <span class="badge badge-important">Off</span>
                    @endif
                </i>
            </div>
        </div>
        <!-- Filters div -->
        <div class="js-stools-container-filters clearfix {{ $filterVisible }}">
            <div class="js-stools-field-filter">
                {!! $time1SelectList !!}
            </div>
            <div class="js-stools-field-filter">
                {!! $time2SelectList !!}
            </div>
            <div class="js-stools-field-filter">
                {!! $deviceSelectList !!}
            </div>
            <div class="js-stools-field-filter">
                {!! $adapterSelectList !!}
            </div>
            <div class="js-stools-field-filter">
                {!! $httpRequestSelectList !!}
            </div>
        </div>
    </div>
@stop

@section('browse-norecords')
    <div class="alert alert-no-items">
        @php echo Text::_( 'COM_JCHOPTIMIZE_NO_RECORDS' ) @endphp
    </div>
@stop
@section('browse-table-header')
    {{-- Header row --}}
    <tr>
        <th>
            <input type="checkbox" name="checkall-toggle" class="form-check-input" onclick="Joomla.checkAll(this)"
                   data-bs-toggle="tooltip" title="Select all items">
        </th>
        <th>
            <a href="#" onclick="return false;" class="js-stools-column-order hasPopover"
               data-order="mtime" data-direction="ASC" data-name="Last modified time"
               title="Last modified time" data-content="Click to sort by this column" data-placement="top">
                {{Text::_('COM_JCHOPTIMIZE_PAGECACHE_MTIME')}} {!! $mtimeSelected !!}
            </a>
        </th>
        <th>
            <a href="#" onclick="return false;" class="js-stools-column-order hasPopover"
               data-order="url" data-direction="ASC" data-name="Page URL"
               title="Page URL" data-content="Click to sort by this column" data-placement="top">
                {{Text::_('COM_JCHOPTIMIZE_PAGECACHE_URL')}} {!! $urlSelected !!}
            </a>
        </th>
        <th style="text-align: center;">
            <a href="#" onclick="return false;" class="js-stools-column-order hasPopover"
               data-order="device" data-direction="ASC" data-name="Device"
               title="Device" data-content="Click to sort by this column" data-placement="top">
                {{Text::_('COM_JCHOPTIMIZE_PAGECACHE_DEVICE')}} {!! $deviceSelected !!}
            </a>
        </th>
        <th>
            <a href="#" onclick="return false;" class="js-stools-column-order hasPopover"
               data-order="adapter" data-direction="ASC" data-name="Adapter"
               title="Adapter" data-content="Click to sort by this column" data-placement="top">
                {{Text::_('COM_JCHOPTIMIZE_PAGECACHE_ADAPTER')}} {!! $adapterSelected !!}
            </a>
        </th>
        <th style="text-align: center;">
            <a href="#" onclick="return false;" class="js-stools-column-order hasPopover"
               data-order="http-request" data-direction="ASC" data-name="HTTP Request"
               title="HTTP Request" data-content="Click to sort by this column" data-placement="top">
                {{Text::_('COM_JCHOPTIMIZE_PAGECACHE_HTTP_REQUEST')}} {!! $httpRequestSelected !!}
            </a>
        </th>
        <th class="hidden-phone hidden-tablet d-none d-sm-none d-md-none d-lg-table-cell">
            <a href="#" onclick="return false;" class="js-stools-column-order hasPopover"
               data-order="id" data-direction="ASC" data-name="Cache ID"
               title="Cache ID" data-content="Click to sort by this column" data-placement="top">
            {{Text::_('COM_JCHOPTIMIZE_PAGECACHE_ID')}} {!! $idSelected !!}
            </a>
        </th>
    </tr>
@stop

