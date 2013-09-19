<?php
namespace Ouzo\Db\Dialect;

use Ouzo\Db\Query;
use Ouzo\Db\QueryType;
use Ouzo\Utilities\FluentArray;

class MysqlDialect extends Dialect
{
    public function buildQuery(Query $query)
    {
        $this->_query = $query;

        $sql = DialectUtil::buildQueryPrefix($query->type);
        $sql .= $this->select();
        $sql .= $this->from();
        $sql .= $this->join();
        $sql .= $this->where();
        $sql .= $this->order();
        $sql .= $this->limit();
        $sql .= $this->offset();

        return rtrim($sql);
    }

    public function from()
    {
        return $this->_buildFromForDelete($this->_query->type, $this->_query->table);
    }

    private function _buildFromForDelete($type, $table)
    {
        return ' FROM ' . $table . ($type == QueryType::$DELETE ? '' : ' AS main');
    }
}