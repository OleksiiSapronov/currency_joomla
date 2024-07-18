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


use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted Access');

FormHelper::loadFieldClass('list');

class JFormFieldProonlyjchcachehandler extends JFormFieldList
{
    public $type = 'proonlyjchcachehandler';

    protected function getOptions()
    {
        $optionsMap = [
            'file' => 'filesystem',
            'redis' => 'redis',
            'apcu' => 'apcu',
            'memcached' => 'memcached',
            'wincache' => 'wincache'
        ];

        $availableStores = Cache::getStores();

        foreach ($optionsMap as $joomlaStorage => $laminasStorage) {
            if (JCH_PRO || $laminasStorage == 'filesystem') {
                if (in_array($joomlaStorage, $availableStores)) {
                    $options[] = HTMLHelper::_(
                        'select.option',
                        $laminasStorage,
                        Text::_('COM_JCHOPTIMIZE_STORAGE_' . strtoupper($laminasStorage)),
                        'value',
                        'text',
                        false
                    );
                } else {
                    $options[] = HTMLHelper::_(
                        'select.option',
                        $laminasStorage,
                        Text::_('COM_JCHOPTIMIZE_STORAGE_' . strtoupper($laminasStorage)),
                        'value',
                        'text',
                        true
                    );
                }
            } else {
                $options[] = HTMLHelper::_(
                    'select.option',
                    $laminasStorage,
                    Text::_('COM_JCHOPTIMIZE_STORAGE_' . strtoupper($laminasStorage)) . ' (Pro Only)',
                    [
                        'disable' => true,
                    ]
                );
            }
        }

        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
