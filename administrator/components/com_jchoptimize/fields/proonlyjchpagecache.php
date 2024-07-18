<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads
 *
 * @package   jchoptimize/core
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2023 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */


use JchOptimize\ContainerFactory;
use JchOptimize\Core\Admin\Helper;
use JchOptimize\Model\ModeSwitcher;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted Access');

FormHelper::loadFieldClass('list');

class JFormFieldProonlyjchpagecache extends JFormFieldList
{
    public $type = 'proonlyjchpagecache';

    protected function getInput()
    {
        if (!JCH_PRO) {
            return Helper::proOnlyField();
        }

        return parent::getInput();
    }

    protected function getOptions(): array
    {
        /** @var ModeSwitcher $modeSwitcher */
        $modeSwitcher = ContainerFactory::getContainer()->get(ModeSwitcher::class);
        $availablePlugins = $modeSwitcher->getAvailablePageCachePlugins();

        foreach ($modeSwitcher->pageCachePlugins as $pageCache => $title) {
            if (in_array($pageCache, $availablePlugins)) {
                $options[] = HTMLHelper::_(
                    'select.option',
                    $pageCache,
                    Text::_($title),
                    'value',
                    'text',
                    false
                );
            } else {
                $options[] = HTMLHelper::_(
                    'select.option',
                    $pageCache,
                    Text::_($title),
                    'value',
                    'text',
                    true
                );
            }
        }

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
