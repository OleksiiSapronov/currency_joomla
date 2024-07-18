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

<ul class="nav nav-tabs">
    <li class="nav-item @php echo $view == 'ControlPanel' ? 'active': '' @endphp">
        <a class="nav-link @php echo $view == 'ControlPanel' ? 'active': '' @endphp" href="index.php?option=com_jchoptimize">
            {{Text::_('COM_JCHOPTIMIZE_TOOLBAR_LABEL_CONTROLPANEL')}}
        </a>
    </li>
    <li class="nav-item @php echo $view == 'OptimizeImages' ? 'active': '' @endphp">
        <a class="nav-link @php echo $view == 'OptimizeImages' ? 'active': '' @endphp" href="index.php?option=com_jchoptimize&amp;view=OptimizeImages">
            {{Text::_('COM_JCHOPTIMIZE_TOOLBAR_LABEL_OPTIMIZEIMAGE')}}
        </a>
    </li>
    <li class="nav-item @php echo $view == 'PageCache' ? 'active': '' @endphp">
        <a class="nav-link @php echo $view == 'PageCache' ? 'active' : '' @endphp" href="index.php?option=com_jchoptimize&amp;view=PageCache">
            {{Text::_('COM_JCHOPTIMIZE_TOOLBAR_LABEL_PAGECACHE')}}
        </a>
    </li>
</ul>

