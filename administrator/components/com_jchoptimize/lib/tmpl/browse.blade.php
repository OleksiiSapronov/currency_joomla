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

    defined( '_JEXEC' ) or die( 'Restricted Access' );

@endphp

{{-- Allow tooltips, used in grid headers --}}
@if (version_compare(JVERSION, '3.999.999', 'le'))
    {{-- @jhtml('behavior.tooltip') --}}
@endif
{{-- Allow SHIFT+click to select multiple rows --}}
{{-- @jhtml('behavior.multiselect') --}}

@section('navigation')
    {{-- Navigation --}}
@stop

@section('browse-filters')
    {{-- Filters above the table. --}}
@stop

@section('browse-table-header')
    {{-- Table header. Column headers and optional filters displayed above the column headers. --}}
@stop

@section('browse-norecords')
    {{-- Table body shown when no records are present. --}}
    @php echo JText::_( 'COM_JCHOPTIMIZE_NO_RECORDS' ) @endphp
@stop

@section('browse-table-body-withrecords')
    {{-- Table body shown when records are present. --}}
    @php $i = 0; @endphp
    @foreach($items as $row)
        <tr>
            {{-- You need to implement me! --}}
        </tr>
    @endforeach
@stop

@section('browse-table-footer')
    {{-- Table footer. The default is showing the pagination footer. --}}
    <tr>
        <td colspan="99" class="center">
            {{-- $this->pagination->getListFooter() --}}
        </td>
    </tr>
@stop

@section('browse-hidden-fields')
    {{-- Put your additional hidden fields in this section --}}
@stop

@yield('browse-page-top')

@yield('navigation')
{{-- Administrator form for browse views --}}
<form action="index.php" method="post" name="adminForm" id="adminForm">
    <div id="j-main-container" class="j-main-container">
        {{-- Filters and ordering --}}
        @yield('browse-filters')

        @unless(count($items))
            @yield('browse-norecords')
        @else
            <div style="overflow-x:auto">
                <table class="@yield('table_class')" id="itemsList">
                    <thead>
                    @yield('browse-table-header')
                    </thead>
                    <tfoot>
                    @yield('browse-table-footer')
                    </tfoot>
                    <tbody>
                    @yield('browse-table-body-withrecords')
                    </tbody>
                </table>
            </div>
        @endunless

        {{-- Hidden form fields --}}
        <div>
            @section('browse-default-hidden-fields')
                <input type="hidden" name="boxchecked" id="boxchecked" value="0"/>
            @show
            @yield('browse-hidden-fields')
        </div>
    </div>
</form>
