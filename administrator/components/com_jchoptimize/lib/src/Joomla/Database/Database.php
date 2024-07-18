<?php

/**
 * JCH Optimize - Performs several front-end optimizations for fast downloads.
 *
 * @author    Samuel Marshall <samuel@jch-optimize.net>
 * @copyright Copyright (c) 2022 Samuel Marshall / JCH Optimize
 * @license   GNU/GPLv3, or later. See LICENSE file
 *
 *  If LICENSE file missing, see <http://www.gnu.org/licenses/>.
 */

namespace JchOptimize\Joomla\Database;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

\defined('_JEXEC') or exit('Restricted Access');

/**
 * Decorator for DatabaseInterface to use in Joomla3.
 */
class Database implements DatabaseInterface
{
    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Call static methods magically.
     *
     * @param string $name Name of method
     * @param array  $args Arguments
     *
     * @return mixed
     */
    public static function __callStatic(string $name, array $args)
    {
        return Factory::getDbo()::$name(\implode(',', $args));
    }

    /**
     * Call any other method magically.
     *
     * @param string $name Name of method
     * @param array  $args Arguments
     *
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        return $this->db->{$name}(\implode(',', $args));
    }

    public static function isSupported(): bool
    {
        return Factory::getDbo()::isSupported();
    }

    public function connect()
    {
        $this->db->connect();
    }

    public function connected(): bool
    {
        return $this->db->connected();
    }

    public function createDatabase($options, $utf = \true)
    {
        return $this->db->createDatabase($options, $utf);
    }

    public function decodeBinary($data): string
    {
        return $data;
    }

    public function disconnect()
    {
        $this->db->disconnect();
    }

    public function dropTable($table, $ifExists = \true)
    {
        return $this->dropTable($table, $ifExists);
    }

    public function escape($text, $extra = \false): string
    {
        return $this->db->escape($text, $extra);
    }

    public function execute()
    {
        return $this->db->execute();
    }

    public function getAffectedRows(): int
    {
        return $this->db->getAffectedRows();
    }

    public function getCollation()
    {
        return $this->db->getCollation();
    }

    public function getConnection()
    {
        return $this->db->getConnection();
    }

    public function getConnectionCollation(): string
    {
        return $this->db->getConnectionCollation();
    }

    public function getConnectionEncryption(): string
    {
        return '';
    }

    public function isConnectionEncryptionSupported(): bool
    {
        return \false;
    }

    public function isMinimumVersion(): bool
    {
        return $this->db->isMinimumVersion();
    }

    public function getCount(): int
    {
        return $this->db->getCount();
    }

    public function getDateFormat(): string
    {
        return $this->db->getDateFormat();
    }

    public function getMinimum(): string
    {
        return $this->db->getMinimum();
    }

    public function getName(): string
    {
        return $this->db->getName();
    }

    public function getNullDate(): string
    {
        return $this->db->getNullDate();
    }

    public function getNumRows($cursor = null): int
    {
        return $this->db->getNumRows($cursor);
    }

    public function getQuery($new = \false)
    {
        return $this->db->getQuery($new);
    }

    public function getServerType(): string
    {
        return $this->db->getServerType();
    }

    public function getTableColumns($table, $typeOnly = \true): array
    {
        return $this->db->getTableColumns($table, $typeOnly);
    }

    public function getTableKeys($tables): array
    {
        return $this->db->getTableKeys($tables);
    }

    public function getTableList(): array
    {
        return $this->db->getTableList();
    }

    public function getVersion(): string
    {
        return $this->db->getVersion();
    }

    public function hasUtfSupport(): bool
    {
        return $this->db->hasUtfSupport();
    }

    public function insertid()
    {
        return $this->db->insertid();
    }

    public function insertObject($table, &$object, $key = null): bool
    {
        return $this->db->insertObject($table, $object, $key);
    }

    public function loadAssoc()
    {
        return $this->db->loadAssoc();
    }

    public function loadAssocList($key = null, $column = null)
    {
        return $this->db->loadAssocList($key, $column);
    }

    public function loadColumn($offset = 0)
    {
        return $this->db->loadColumn($offset);
    }

    public function loadObject($class = \stdClass::class)
    {
        return $this->db->loadObject($class);
    }

    public function loadObjectList($key = '', $class = \stdClass::class)
    {
        return $this->db->loadObjectList($key, $class);
    }

    public function loadResult()
    {
        return $this->db->loadResult();
    }

    public function loadRow()
    {
        return $this->db->loadRow();
    }

    public function loadRowList($key = null)
    {
        return $this->db->loadRowList($key);
    }

    public function lockTable($tableName)
    {
        return $this->db->lockTable($tableName);
    }

    public function quote($text, $escape = \true)
    {
        return $this->db->quote($text, $escape);
    }

    public function quoteBinary($data): string
    {
        return $this->db->quoteBinary($data);
    }

    public function quoteName($name, $as = null)
    {
        return $this->db->quoteName($name, $as);
    }

    public function renameTable($oldTable, $newTable, $backup = null, $prefix = null)
    {
        return $this->db->renameTable($oldTable, $newTable, $backup, $prefix);
    }

    public function replacePrefix($sql, $prefix = '#__'): string
    {
        return $this->db->replacePrefix($sql, $prefix);
    }

    public function select($database): bool
    {
        return $this->db->select($database);
    }

    public function setQuery($query, $offset = 0, $limit = 0)
    {
        return $this->db->setQuery($query, $offset, $limit);
    }

    public function transactionCommit($toSavepoint = \false)
    {
        $this->db->transactionCommit($toSavepoint);
    }

    public function transactionRollback($toSavepoint = \false)
    {
        $this->db->transactionRollback($toSavepoint);
    }

    public function transactionStart($asSavepoint = \false)
    {
        $this->db->transactionStart($asSavepoint);
    }

    public function truncateTable($table)
    {
        $this->db->truncateTable($table);
    }

    public function unlockTables()
    {
        return $this->db->unlockTables();
    }

    public function updateObject($table, &$object, $key, $nulls = \false): bool
    {
        return $this->db->updateObject($table, $object, $key, $nulls);
    }
}
