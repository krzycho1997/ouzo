<?php
/*
 * Copyright (c) Ouzo contributors, http://ouzoframework.org
 * This file is made available under the MIT License (view the LICENSE file for more information).
 */

namespace Ouzo\Db;

use Ouzo\Db\WhereClause\WhereClause;
use Ouzo\DbException;
use Ouzo\Restriction\Restriction;
use PDO;

class Query
{
    public Query|string|null $table = null;
    public ?string $aliasTable = null;
    public bool $distinct = false;
    /** @var string[] */
    public ?array $selectColumns = [];
    public int $selectType = PDO::FETCH_ASSOC;
    public string|array|null $order = null;
    public ?int $limit = null;
    public ?int $offset = null;
    public array $updateAttributes = [];
    public array $upsertConflictColumns = [];
    /** @var WhereClause[] */
    public array $whereClauses = [];
    /** @var JoinClause[] */
    public array $joinClauses = [];
    /** @var JoinClause[] */
    public array $usingClauses = [];
    public ?int $type;
    public array $options = [];
    public string|array|null $groupBy = null;
    public bool $lockForUpdate = false;
    public ?string $comment = null;

    public function __construct(?int $type = null)
    {
        $this->type = $type ? $type : QueryType::$SELECT;
    }

    /**
     * @param int|null $type
     * @return Query
     */
    public static function newInstance($type = null)
    {
        return new Query($type);
    }

    /**
     * @param array $attributes
     * @return Query
     */
    public static function insert($attributes)
    {
        return Query::newInstance(QueryType::$INSERT)->attributes($attributes);
    }

    /**
     * @param array $attributes
     * @return Query
     */
    public static function insertOrDoNoting($attributes)
    {
        return Query::newInstance(QueryType::$INSERT_OR_DO_NOTHING)->attributes($attributes);
    }

    /**
     * @param array $attributes
     * @return Query
     */
    public static function update($attributes)
    {
        return Query::newInstance(QueryType::$UPDATE)->attributes($attributes);
    }

    /**
     * @param array $attributes
     * @return Query
     */
    public static function upsert($attributes)
    {
        return Query::newInstance(QueryType::$UPSERT)->attributes($attributes);
    }

    /**
     * @param array|null $selectColumns
     * @return Query
     */
    public static function select(array $selectColumns = null)
    {
        $query = new Query();
        $query->selectColumns = $selectColumns;
        return $query;
    }

    /**
     * @param array|null $selectColumns
     * @return Query
     */
    public static function selectDistinct(array $selectColumns = null)
    {
        $query = self::select($selectColumns);
        $query->distinct = true;
        return $query;
    }

    /**
     * @return Query
     */
    public static function count()
    {
        return new Query(QueryType::$COUNT);
    }

    /**
     * @return Query
     */
    public static function delete()
    {
        return new Query(QueryType::$DELETE);
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function attributes($attributes)
    {
        $this->updateAttributes = $attributes;
        return $this;
    }

    /**
     * @param string $table
     * @return $this
     */
    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @param string $table
     * @return Query
     */
    public function into($table)
    {
        return $this->table($table);
    }

    /**
     * @param string $table
     * @param string|null $alias
     * @return Query
     */
    public function from($table, $alias = null)
    {
        $this->aliasTable = $alias;
        return $this->table($table);
    }

    /**
     * @param string $order
     * @return $this
     */
    public function order($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function where(array|string|WhereClause $where = '', array|string|null $whereValues = null): static
    {
        $this->validateParameters($where);
        $this->whereClauses[] = WhereClause::create($where, $whereValues);
        return $this;
    }

    /**
     * @param JoinClause $usingClause
     * @return $this
     */
    public function addUsing(JoinClause $usingClause)
    {
        $this->usingClauses[] = $usingClause;
        return $this;
    }

    /**
     * @param string $joinTable
     * @param string $joinKey
     * @param string $idName
     * @param string|null $alias
     * @param string $type
     * @param array $on
     * @return $this
     */
    public function join($joinTable, $joinKey, $idName, $alias = null, $type = 'LEFT', $on = [])
    {
        $onClauses = [WhereClause::create($on)];
        $this->joinClauses[] = new JoinClause($joinTable, $joinKey, $idName, $this->aliasTable ?: $this->table, $alias, $type, $onClauses);
        return $this;
    }

    /**
     * @param JoinClause $join
     * @return $this
     */
    public function addJoin(JoinClause $join)
    {
        $this->joinClauses[] = $join;
        return $this;
    }

    /**
     * @param string $groupBy
     * @return $this
     */
    public function groupBy($groupBy)
    {
        $this->groupBy = $groupBy;
        return $this;
    }

    /**
     * @return $this
     */
    public function lockForUpdate()
    {
        $this->lockForUpdate = true;
        return $this;
    }

    /**
     * @param string $comment
     * @return $this
     */
    public function comment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @param array $upsertConflictColumns
     * @return $this
     */
    public function onConflict(array $upsertConflictColumns = [])
    {
        $this->upsertConflictColumns = $upsertConflictColumns;
        return $this;
    }

    /**
     * @param array $where
     * @throws DbException
     */
    private function validateParameters($where)
    {
        if (is_array($where)) {
            foreach ($where as $key => $value) {
                if (is_object($value) && !($value instanceof Restriction)) {
                    throw new DbException('Cannot bind object as a parameter for "' . $key . '".');
                }
            }
        }
    }
}
