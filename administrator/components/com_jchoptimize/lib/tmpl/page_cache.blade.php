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

    use Joomla\CMS\HTML\HTMLHelper;

    defined( '_JEXEC' ) or die( 'Restricted Access' );

    $options = [
        'orderFieldSelector' => '#list_fullordering',
        'limitFieldSelector' => '#list_limit',
        'searchBtnSelector' => '.filter-search-bar__button',
        'filtersHidden' => $filtersHidden
    ];

    HTMLHelper::_( 'searchtools.form', '#adminForm', $options );

use function _JchOptimizeVendor\e;

@endphp

@if(!JCH_PRO)
    <script>
        document.querySelector('#toolbar-share button.button-share').disabled = true;
    </script>
@endif

@if (version_compare(JVERSION, '3.999.999', 'le'))
    @include('navigation')
@endif

@extends('browse')

@if(version_compare(JVERSION, '4', 'lt'))
    @include('page_cache_j3')
@else
    @include('page_cache_j4')
@endif

@section('browse-table-body-withrecords')
    {{--Table body when records are present --}}
    @php $i = 0; @endphp
    @foreach($items as $item)
        <tr>
            <td>
                <input type="checkbox" id="cb{{$i++}}" name="cid[]" value="{{$item['id']}}"
                       onclick="Joomla.isChecked(this.checked)" class="form-check-input">
            </td>
            <td>
                {{date('l, F d, Y h:i:s A', $item['mtime'])}} GMT
            </td>
            <td>
                <a title="{{$item['url']}}" href="{{$item['url']}}" class="page-cache-url" target="_blank">{{$item['url']}}</a>
            </td>
            <td style="text-align: center;">
                @if($item['device'] == 'Desktop')
                    <span class="fa fa-desktop" data-bs-toggle="tooltip" title="{{$item['device']}}"></span>
                @else
                    <span class="fa fa-mobile-alt" data-bs-toggle="tooltip" title="{{$item['device']}}"></span>
                @endif
            </td>
            <td>
                {{$item['adapter']}}
            </td>
            <td style="text-align: center;">
                @if($item['http-request'] == 'yes')
                    <span class="fa fa-check-circle" style="color: green;"></span>
                @else
                    <span class="fa fa-times-circle" style="color: firebrick;"></span>
                @endif
            </td>
            <td class="hidden-phone hidden-tablet d-none d-sm-none d-md-none d-lg-none d-xl-none d-xxl-table-cell">
                <span class="page-cache-id">{{$item['id']}}</span>
            </td>
        </tr>
    @endforeach
@stop

@section('browse-table-footer')
    <tr>
        <td colspan="99">

            @if($paginator->pageCount > 1 )
                <nav aria-label="pagination" class="pagination justify-content-center" style="text-align: center;">
                    <ul class="pagination justify-content-center">
                        {{--Previous and start page link --}}
                        @if(isset($paginator->previous))
                            <li class="page-item">
                                <a class="page-link" href="{{$pageLink}}&list_page={{$paginator->first}}">Start</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link"
                                   href="{{$pageLink}}&list_page={{$paginator->previous}}">Previous</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Start</a>
                            </li>

                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                            </li>
                        @endif

                        {{-- Numbered page links --}}
                        @foreach($paginator->pagesInRange as $page)
                            @if($page != $paginator->current)
                                <li class="page-item">
                                    <a class="page-link" href="{{$pageLink}}&list_page={{$page}}">{{$page}}</a>
                                </li>
                            @else
                                <li class="page-item active" aria-current="page">
                                    <a class="page-link" href="{{$pageLink}}&list_page={{$page}}">{{$page}}</a>
                                </li>
                            @endif
                        @endforeach

                        {{-- Next and last page link --}}
                        @if(isset($paginator->next))
                            <li class="page-item">
                                <a class="page-link" href="{{$pageLink}}&list_page={{$paginator->next}}">Next</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link"
                                   href="{{$pageLink}}&list_page={{$paginator->last}}">End</a>
                            </li>
                        @else
                            <li class="page-item disabled">
                                <a class="page-link" href="#">Next</a>
                            </li>
                            <li class="page-item disabled">
                                <a class="page-link" href="#">End</a>
                            </li>
                        @endif
                    </ul>
                </nav>

            @endif

        </td>
    </tr>
@stop

@section('browse-hidden-fields')
    {{--Add these hidden fields to the default --}}
    <input type="hidden" name="option" id="option" value="com_jchoptimize"/>
    <input type="hidden" name="view" id="view" value="PageCache"/>
    <input type="hidden" name="task" id="task" value=""/>
    {!! HTMLHelper::_('form.token') !!}
@stop

