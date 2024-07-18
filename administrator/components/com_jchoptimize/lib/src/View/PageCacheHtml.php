<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\View;

use _JchOptimizeVendor\Joomla\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper as JToolbarHelper;
use Joomla\CMS\Uri\Uri as JUri;
use Joomla\Registry\Registry;

\defined('_JEXEC') or exit('Restricted Access');
class PageCacheHtml extends HtmlView
{
    public function renderStatefulElements(Registry $state): void
    {
        // Generate HTML for Search input box
        // language=HTML
        /** @var string $filterSearchValue */
        $filterSearchValue = $state->get('filter_search', '');
        $searchInput = '<input type="text" name="filter[search]" id="filter_search" value="'.$filterSearchValue.'" class="form-control js-stools-field-search" aria-describedby="filter_search-desc" placeholder="Search by URL" inputmode="search">';
        $this->addData('searchInput', $searchInput);
        // Generate HTML for filter time 1
        $time1Options = ['' => '- Filter by Start Time -', '900' => '>= 15 mins ago', '1800' => '>= 30 mins ago ', '3600' => '>= 1 hour ago', '10800' => '>= 3 hours ago', '21600' => '>= 6 hours ago', '43200' => '>= 12 hours ago', '86400' => '>= 1 day ago', '172800' => '>= 2 days ago', '604800' => '>= 1 week ago', '1209600' => '>= 2 weeks ago'];
        $time1SelectList = $this->selectListGenerator($state, 'filter', $time1Options, 'time-1');
        // Generate HTML for filter time 2
        $time2Options = ['' => '- Filter by End Time -', '900' => '< 15 mins ago', '1800' => '< 30 mins ago', '3600' => '< 1 hour ago', '10800' => '< 3 hours ago', '21600' => '< 6 hours ago', '43200' => '< 12 hours ago', '86400' => '< 1 day ago', '172800' => '< 2 days ago', '604800' => '< 1 week ago', '1209600' => '< 2 weeks ago'];
        $time2SelectList = $this->selectListGenerator($state, 'filter', $time2Options, 'time-2');
        // Generate HTML for filter device
        $deviceOptions = ['' => '- Filter by Device -', 'Mobile' => 'Mobile', 'Desktop' => 'Desktop'];
        $deviceSelectList = $this->selectListGenerator($state, 'filter', $deviceOptions, 'device');
        // Generate HTML for filter adapter
        $adapterOptions = ['' => '- Filter by Adapter -', 'Filesystem' => 'Filesystem', 'Redis' => 'Redis', 'Apcu' => 'APCu', 'Memcached' => 'Memcached', 'Wincache' => 'Wincache'];
        $adapterSelectList = $this->selectListGenerator($state, 'filter', $adapterOptions, 'adapter');
        // Generate HTML for filter HTTP Request
        $httpRequestOptions = ['' => '- Filter by HTTP Request -', 'yes' => 'Yes', 'no' => 'No'];
        $httpRequestSelectList = $this->selectListGenerator($state, 'filter', $httpRequestOptions, 'http-request');
        $filterVisible = '';
        $filtersHidden = \true;
        if ($state->get('filter_time-1') || $state->get('filter_time-2') || $state->get('filter_device') || $state->get('filter_adapter') || $state->get('filter_http-request')) {
            $filterVisible = 'js-stools-container-filters-visible';
            $filtersHidden = \false;
        }

        /** @var string $fullOrderingState */
        $fullOrderingState = $state->get('list_fullordering', '');
        $this->addData('time1SelectList', $time1SelectList);
        $this->addData('time2SelectList', $time2SelectList);
        $this->addData('deviceSelectList', $deviceSelectList);
        $this->addData('adapterSelectList', $adapterSelectList);
        $this->addData('httpRequestSelectList', $httpRequestSelectList);
        $this->addData('deviceSelected', $this->getIcon('device', $fullOrderingState));
        $this->addData('urlSelected', $this->getIcon('url', $fullOrderingState));
        $this->addData('mtimeSelected', $this->getIcon('mtime', $fullOrderingState));
        $this->addData('adapterSelected', $this->getIcon('adapter', $fullOrderingState));
        $this->addData('httpRequestSelected', $this->getIcon('http-request', $fullOrderingState));
        $this->addData('idSelected', $this->getIcon('id', $fullOrderingState));
        $this->addData('filterVisible', $filterVisible);
        $this->addData('filtersHidden', $filtersHidden);
        // Generate HTML for Sort ordering list
        $fullOrderingOptions = ['' => '- Sort Table By: -', 'mtime ASC' => 'Last modified time ascending', 'mtime DESC' => 'Last modified time descending', 'url ASC' => 'Page URL ascending', 'url DESC' => 'Page URL descending', 'device ASC' => 'Device ascending', 'device DESC' => 'Device descending', 'adapter ASC' => 'Adapter ascending', 'adapter DESC' => 'Adapter descending', 'http-request ASC' => 'HTTP Request ascending', 'http-request DESC' => 'HTTP Request descending', 'id ASC' => 'Cache ID ascending', 'id desc' => 'Cache ID descending'];
        $orderingSelectList = $this->selectListGenerator($state, 'list', $fullOrderingOptions, 'fullordering');
        $this->addData('orderingSelectList', $orderingSelectList);
        // Generate HTML for list limit
        $limitOptions = ['5' => '5', '10' => '10', '15' => '15', '20' => '20', '25' => '25', '30' => '30', '50' => '50', '100' => '100', '200' => '200', '500' => '500', '-1' => 'All'];

        /** @var null|string $defaultListLimit */
        $defaultListLimit = Factory::getApplication()->get('list_limit');
        $limitList = $this->selectListGenerator($state, 'list', $limitOptions, 'limit', $defaultListLimit);
        $this->addData('limitList', $limitList);
    }

    public function loadResources(): void
    {
        HTMLHelper::_('bootstrap.tooltip', '[data-bs-toggle="tooltip"]', ['placement' => 'right']);
        $document = Factory::getDocument();
        $options = ['version' => JCH_VERSION];
        $document->addStyleSheet(JUri::root(\true).'/media/com_jchoptimize/css/admin-joomla.css', $options);
        $document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
    }

    public function loadToolBar(): void
    {
        JToolbarHelper::title(Text::_(JCH_PRO ? 'COM_JCHOPTIMIZE_PRO' : 'COM_JCHOPTIMIZE'), 'dashboard');
        JToolbarHelper::deleteList();
        JToolbarHelper::custom('deleteAll', 'remove', '', 'JTOOLBAR_DELETE_ALL', \false);
        if (JCH_PRO) {
            $alt = 'COM_JCHOPTIMIZE_RECACHE';
        } else {
            $alt = 'COM_JCHOPTIMIZE_RECACHE_PROONLY';
        }
        JToolbarHelper::custom('recache', 'share', '', $alt, \false);
        JToolbarHelper::preferences('com_jchoptimize');
    }

    /**
     * @param string[] $options
     */
    private function selectListGenerator(Registry $state, string $type, array $options, string $value, ?string $defaultValue = null): string
    {
        $attribs = ['class' => 'form-select', 'onchange' => 'this.form.submit()'];
        /** @var string $listGeneratorHtml */
        return HTMLHelper::_('select.genericlist', $options, "{$type}[{$value}]", $attribs, 'value', 'text', $state->get("{$type}_{$value}", $defaultValue), "{$type}_{$value}");
    }

    /**
     * @return string[]
     */
    private function getIcon(string $orderBy, string $state): array
    {
        if (\version_compare(JVERSION, '4', 'lt')) {
            return [$this->getIconJ3($orderBy, $state)];
        }
        $default = '<span class="ms-1 icon-sort" aria-hidden="true"></span>';
        if (!$state) {
            return [$default, '', ''];
        }
        [$fullordering, $direction] = \explode(' ', $state);
        if ($orderBy != $fullordering) {
            return [$default, '', ''];
        }
        if ('ASC' == $direction) {
            return ['<span class="ms-1 icon-caret-up" aria-hidden="true"></span>', 'selected', 'id="sorted"'];
        }
        if ('DESC' == $direction) {
            return ['<span class="ms-1 icon-caret-down" aria-hidden="true"></span>', 'selected', 'id="sorted"'];
        }

        return [$default, '', ''];
    }

    private function getIconJ3(string $orderBy, string $state): string
    {
        if (!$state) {
            return '';
        }
        [$fullordering, $direction] = \explode(' ', $state);
        if ($orderBy != $fullordering) {
            return '';
        }
        if ('ASC' == $direction) {
            return '<span class="icon-arrow-up-3"></span>';
        }
        if ('DESC' == $direction) {
            return '<span class="icon-arrow-down-3"></span>';
        }

        return '';
    }
}
