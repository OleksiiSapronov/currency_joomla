<?php

namespace _JchOptimizeVendor\Laminas\Paginator\Adapter;

use _JchOptimizeVendor\Laminas\Db\Adapter\Adapter;
use _JchOptimizeVendor\Laminas\Db\ResultSet\ResultSet;
use _JchOptimizeVendor\Laminas\Db\ResultSet\ResultSetInterface;
use _JchOptimizeVendor\Laminas\Db\Sql\Expression;
use _JchOptimizeVendor\Laminas\Db\Sql\Select;
use _JchOptimizeVendor\Laminas\Db\Sql\Sql;
use _JchOptimizeVendor\Laminas\Paginator\Adapter\Exception\MissingRowCountColumnException;

/**
 * @deprecated 2.10.0 Use the adapters in laminas/laminas-paginator-adapter-laminasdb.
 */
class DbSelect implements AdapterInterface
{
    public const ROW_COUNT_COLUMN_NAME = 'C';

    /** @var Sql */
    protected $sql;

    /**
     * Database query.
     *
     * @var Select
     */
    protected $select;

    /**
     * Database count query.
     *
     * @var null|Select
     */
    protected $countSelect;

    /** @var ResultSet */
    protected $resultSetPrototype;

    /**
     * Total item count.
     *
     * @var int
     */
    protected $rowCount;

    /**
     * @param Select      $select             The select query
     * @param Adapter|Sql $adapterOrSqlObject DB adapter or Sql object
     *
     * @throws Exception\InvalidArgumentException
     */
    public function __construct(Select $select, $adapterOrSqlObject, ?ResultSetInterface $resultSetPrototype = null, ?Select $countSelect = null)
    {
        $this->select = $select;
        $this->countSelect = $countSelect;
        if ($adapterOrSqlObject instanceof Adapter) {
            $adapterOrSqlObject = new Sql($adapterOrSqlObject);
        }
        if (!$adapterOrSqlObject instanceof Sql) {
            throw new Exception\InvalidArgumentException('_JchOptimizeVendor\\$adapterOrSqlObject must be an instance of Laminas\\Db\\Adapter\\Adapter or Laminas\\Db\\Sql\\Sql');
        }
        $this->sql = $adapterOrSqlObject;
        $this->resultSetPrototype = $resultSetPrototype ?: new ResultSet();
    }

    /**
     * Returns an array of items for a page.
     *
     * @param int $offset           Page offset
     * @param int $itemCountPerPage Number of items per page
     *
     * @return array
     */
    public function getItems($offset, $itemCountPerPage)
    {
        $select = clone $this->select;
        $select->offset($offset);
        $select->limit($itemCountPerPage);
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        $resultSet = clone $this->resultSetPrototype;
        $resultSet->initialize($result);

        return \iterator_to_array($resultSet);
    }

    /**
     * Returns the total number of rows in the result set.
     *
     * @return int
     *
     * @throws MissingRowCountColumnException
     */
    public function count()
    {
        if (null !== $this->rowCount) {
            return $this->rowCount;
        }
        $select = $this->getSelectCount();
        $statement = $this->sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        $row = $result->current();
        $this->rowCount = $this->locateRowCount($row);

        return $this->rowCount;
    }

    /**
     * @internal
     *
     * @see https://github.com/laminas/laminas-paginator/issues/3 Reference for creating an internal cache ID
     *
     * @todo The next major version should rework the entire caching of a paginator.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return ['select' => $this->sql->buildSqlString($this->select), 'count_select' => $this->sql->buildSqlString($this->getSelectCount())];
    }

    /**
     * Returns select query for count.
     *
     * @return Select
     */
    protected function getSelectCount()
    {
        if ($this->countSelect) {
            return $this->countSelect;
        }
        $select = clone $this->select;
        $select->reset(Select::LIMIT);
        $select->reset(Select::OFFSET);
        $select->reset(Select::ORDER);
        $countSelect = new Select();
        $countSelect->columns([self::ROW_COUNT_COLUMN_NAME => new Expression('COUNT(1)')]);
        $countSelect->from(['original_select' => $select]);

        return $countSelect;
    }

    /**
     * @return int
     *
     * @throws MissingRowCountColumnException
     */
    private function locateRowCount(array $row)
    {
        if (\array_key_exists(self::ROW_COUNT_COLUMN_NAME, $row)) {
            return (int) $row[self::ROW_COUNT_COLUMN_NAME];
        }
        $lowerCaseColumnName = \strtolower(self::ROW_COUNT_COLUMN_NAME);
        if (\array_key_exists($lowerCaseColumnName, $row)) {
            return (int) $row[$lowerCaseColumnName];
        }

        throw MissingRowCountColumnException::forColumn(self::ROW_COUNT_COLUMN_NAME);
    }
}
