<?php

namespace _JchOptimizeVendor\Laminas\Paginator\Adapter;

use _JchOptimizeVendor\Laminas\Db\Sql\Having;
use _JchOptimizeVendor\Laminas\Db\Sql\Where;
use _JchOptimizeVendor\Laminas\Db\TableGateway\AbstractTableGateway;

/**
 * @deprecated 2.10.0 Use the adapters in laminas/laminas-paginator-adapter-laminasdb.
 */
class DbTableGateway extends DbSelect
{
    /**
     * Constructs instance.
     *
     * @param null|array|\Closure|string|Where  $where
     * @param null|array|string                 $order
     * @param null|array|string                 $group
     * @param null|array|\Closure|Having|string $having
     */
    public function __construct(AbstractTableGateway $tableGateway, $where = null, $order = null, $group = null, $having = null)
    {
        $sql = $tableGateway->getSql();
        $select = $sql->select();
        if ($where) {
            $select->where($where);
        }
        if ($order) {
            $select->order($order);
        }
        if ($group) {
            $select->group($group);
        }
        if ($having) {
            $select->having($having);
        }
        $resultSetPrototype = $tableGateway->getResultSetPrototype();
        parent::__construct($select, $sql, $resultSetPrototype);
    }
}
