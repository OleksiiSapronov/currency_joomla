<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2021 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Model;

use JchOptimize\Core\Exception;
use JchOptimize\Helper\CacheCleaner;
use JchOptimize\Joomla\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\TableInterface;

\defined('_JEXEC') or exit('Restricted Access');

/**
 * Used in Models that are Database and State aware to save the state to the database.
 */
trait SaveSettingsTrait
{
    protected string $name = 'save_settings';

    /**
     * @throws Exception\ExceptionInterface
     */
    private function saveSettings()
    {
        $table = Table::getInstance('extension', 'JTable', ['dbo' => $this->db]);
        $context = 'com_jchoptimize.'.$this->name;
        $data = ['params' => $this->state->toString()];
        PluginHelper::importPlugin('extension');
        if (\false === $table) {
            throw new Exception\RuntimeException('Table not found');
        }
        \assert($table instanceof TableInterface);
        if (!$table->load(['element' => 'com_jchoptimize', 'type' => 'component'])) {
            throw new Exception\RuntimeException($table->getError());
        }
        if (!$table->bind($data)) {
            throw new Exception\RuntimeException($table->getError());
        }
        if (!$table->check()) {
            throw new Exception\RuntimeException($table->getError());
        }

        try {
            $result = Factory::getApplication()->triggerEvent('onExtensionBeforeSave', [$context, $table, \false]);
        } catch (\Exception $e) {
            $result = [];
        }
        // Store the data.
        if (\in_array(\false, $result, \true) || !$table->store()) {
            throw new Exception\RuntimeException($table->getError());
        }

        try {
            Factory::getApplication()->triggerEvent('onExtensionAfterSave', [$context, $table, \false]);
            CacheCleaner::clearCacheGroups(['_system'], [0, 1]);
        } catch (\Exception $e) {
        }
        PluginHelper::reload();
    }
}
