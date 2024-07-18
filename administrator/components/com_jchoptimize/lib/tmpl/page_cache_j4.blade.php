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

    defined( '_JEXEC' ) or die( 'Restricted Access' );

use function _JchOptimizeVendor\e;

@endphp

@section('table_class', 'table table-hover')

@section('browse-norecords')
    <div class="alert alert-info">
        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden">Info</span>
        @php echo Text::_( 'COM_JCHOPTIMIZE_NO_RECORDS' ) @endphp
    </div>
@stop
@section('browse-filters')
    <div class="js-stools" role="search">
        <div class="p-3">
            <i>Storage: <span class="badge bg-primary">{{$adapter}}</span> </i>
            <i class="ms-1">Http Request:
                @if($httpRequest)
                    <span class="badge bg-success">On</span>
                @else
                    <span class="badge bg-danger">Off</span>
                @endif
            </i>
        </div>
        <div class="js-stools-container-bar">
            <div class="btn-toolbar">
                <div class="filter-search-bar btn-group">
                    <div class="input-group">
                        {!! $searchInput !!}
                        <div role="tooltip" id="filter_search-desc" class="filter-search-bar__description">
                            Search for page cache items using the page URL
                        </div>
                        <span class="filter-search-bar__label visually-hidden">
			<label id="filter_search-lbl" for="filter_search">
	Search Tags</label>
		</span>
                        <button type="submit" class="filter-search-bar__button btn btn-primary" aria-label="Search">
                            <span class="filter-search-bar__button-icon icon-search" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
                <div class="filter-search-actions btn-group">
                    <button type="button" class="filter-search-actions__button btn btn-primary js-stools-btn-filter">
                        Filter Options <span class="icon-angle-down" aria-hidden="true"></span>
                    </button>
                    <button type="button" id="filter-search-clear-button"
                            class="filter-search-actions__button btn btn-primary js-stools-btn-clear">
                        Clear
                    </button>

                </div>
                <div class="ordering-select">
                    <div class="js-stools-field-list">
                        <span class="visually-hidden">
                            <label id="list_fullordering-lbl" for="list_fullordering"> Sort Table By:</label>
                        </span>
                        {!! $orderingSelectList !!}
                    </div>
                    <div class="js-stools-field-list">
                        <span class="visually-hidden">
                            <label id="list_limit-lbl" for="list_limit"> Select number of items per page.</label>
                        </span>
                        {!! $limitList !!}
                    </div>
                </div>
            </div>
        </div>
        <!-- Filters div -->
        <div class="js-stools-container-filters clearfix {{ $filterVisible }}">
            <div class="js-stools-field-filter">
				<span class="visually-hidden"><label id="filter_published-lbl" for="filter_published">
	Time 1</label>
</span>
                {!! $time1SelectList !!}
            </div>
            <div class="js-stools-field-filter">
				<span class="visually-hidden"><label id="filter_published-lbl" for="filter_published">
	Time 2</label>
</span>
                {!! $time2SelectList !!}
            </div>
            <div class="js-stools-field-filter">
				<span class="visually-hidden"><label id="filter_published-lbl" for="filter_published">
	Device</label>
</span>
                {!! $deviceSelectList !!}
            </div>
            <div class="js-stools-field-filter">
				<span class="visually-hidden"><label id="filter_published-lbl" for="filter_published">
	Adapter</label>
</span>
                {!! $adapterSelectList !!}
            </div>
            <div class="js-stools-field-filter">
                <span class="visually-hidden"><label id="filter_published-lbl" for="filter_published">
                        HTTP Request
                    </label>
                </span>
                {!! $httpRequestSelectList !!}
            </div>

        </div>
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
            <a href="" onclick="return false;"
               class="js-stools-column-order {{$mtimeSelected[1]}} js-stools-button-sort"
               data-order="mtime"
               data-direction="ASC"
               data-caption="" {!! $mtimeSelected[2] !!}
            >
                <span>{{Text::_('COM_JCHOPTIMIZE_PAGECACHE_MTIME')}}</span>
                {!! $mtimeSelected[0] !!}
                <span class="visually-hidden">
		Sort Table By:		Last modified time	</span>
            </a>
        </th>
        <th>
            <a href="" onclick="return false;" class="js-stools-column-order {{$urlSelected[1]}} js-stools-button-sort"
               data-order="url"
               data-direction="ASC"
               data-caption="" {!! $urlSelected[2] !!}
            >
                <span>{{Text::_('COM_JCHOPTIMIZE_PAGECACHE_URL')}}</span>
                {!! $urlSelected[0] !!}
                <span class="visually-hidden">
		Sort Table By:		URL	</span>
            </a>
        </th>
        <th style="text-align: center;">
            <a href="" onclick="return false;"
               class="js-stools-column-order {{$deviceSelected[1]}} js-stools-button-sort"
               data-order="device"
               data-direction="ASC"
               data-caption="" {!! $deviceSelected[2] !!}
            >
                <span>{{Text::_('COM_JCHOPTIMIZE_PAGECACHE_DEVICE')}}</span>
                {!! $deviceSelected[0] !!}
                <span class="visually-hidden">
		Sort Table By:		Device	</span>
            </a>
        </th>
        <th>
            <a href="" onclick="return false;"
               class="js-stools-column-order {{$adapterSelected[1]}} js-stools-button-sort"
               data-order="adapter"
               data-direction="ASC"
               data-caption="" {!! $adapterSelected[2] !!}
            >
                <span>{{Text::_('COM_JCHOPTIMIZE_PAGECACHE_ADAPTER')}}</span>
                {!! $adapterSelected[0] !!}
                <span class="visually-hidden">
		Sort Table By:		URL	</span>
            </a>
        </th>
        <th>
            <a href="" onclick="return false;"
               class="js-stools-column-order {{$httpRequestSelected[1]}} js-stools-button-sort"
               data-order="http-request"
               data-direction="ASC"
               data-caption="" {!! $httpRequestSelected[2] !!}
            >
                <span>{{Text::_('COM_JCHOPTIMIZE_PAGECACHE_HTTP_REQUEST')}}</span>
                {!! $httpRequestSelected[0] !!}
                <span class="visually-hidden">
		Sort Table By:		URL	</span>
            </a>
        </th>
        <th class="hidden-phone hidden-tablet d-none d-sm-none d-md-none d-lg-none d-xl-none d-xxl-table-cell">
            <a href="" onclick="return false;"
               class="js-stools-column-order {{$idSelected[1]}} js-stools-button-sort"
               data-order="id"
               data-direction="ASC"
               data-caption="" {!! $idSelected[2] !!}
            >
                <span>{{Text::_('COM_JCHOPTIMIZE_PAGECACHE_ID')}}</span>
                {!! $idSelected[0] !!}
                <span class="visually-hidden">Sort Table By: ID</span>
        </th>
    </tr>
@stop

