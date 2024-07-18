<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2020 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 * If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Platform;

use JchOptimize\ContainerFactory;
use JchOptimize\Core\Exception;
use JchOptimize\Core\Interfaces\Plugin as PluginInterface;
use JchOptimize\Helper\CacheCleaner;
use JchOptimize\Joomla\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

\defined('_JEXEC') or exit('Restricted access');
class Plugin implements PluginInterface
{
    protected static $plugin;

    /**
     * @return int
     *
     * @psalm-suppress NullableReturnStatement
     */
    public static function getPluginId()
    {
        $plugin = static::loadjch();

        return $plugin->extension_id;
    }

    /**
     * @return null|mixed
     */
    public static function getPlugin()
    {
        return static::loadjch();
    }

    /**
     * @deprecated
     */
    public static function getPluginParams()
    {
        return ContainerFactory::getContainer()->get('params');
    }

    public static function saveSettings(Registry $params): void
    {
        $table = Table::getInstance('extension');
        $context = 'com_jchoptimize.plugin';
        $data = ['params' => $params->toString()];
        PluginHelper::importPlugin('extension');
        if (!$table->load(['element' => 'com_jchoptimize', 'type' => 'component'])) {
            throw new Exception\RuntimeException($table->getError());
        }
        if (!$table->bind($data)) {
            throw new Exception\RuntimeException($table->getError());
        }
        if (!$table->check()) {
            throw new Exception\RuntimeException($table->getError());
        }

        /** @var array<array-key, mixed> $result */
        $result = [];

        try {
            $result = Factory::getApplication()->triggerEvent('onExtensionBeforeSave', [$context, $table, \false]);
        } catch (\Exception $e) {
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
    }

    /**
     * @return null|mixed
     */
    private static function loadjch()
    {
        if (null !== self::$plugin) {
            return self::$plugin;
        }
        $db = ContainerFactory::getContainer()->get('db');
        $query = $db->getQuery(\true)->select('folder AS type, element AS name, params, extension_id')->from('#__extensions')->where('type = '.$db->quote('component'))->where('element = '.$db->quote('com_jchoptimize'));
        self::$plugin = $db->setQuery($query)->loadObject();

        return self::$plugin;
    }
}
