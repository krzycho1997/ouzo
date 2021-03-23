<?php
/*
 * Copyright (c) Ouzo contributors, http://ouzoframework.org
 * This file is made available under the MIT License (view the LICENSE file for more information).
 */

namespace Ouzo\Db\Dialect;

use Ouzo\Utilities\Arrays;

class PostgresDialect extends Dialect
{
    public function getConnectionErrorCodes(): array
    {
        return ['57000', '57014', '57P01', '57P02', '57P03'];
    }

    public function getErrorCode(array $errorInfo): mixed
    {
        return Arrays::getValue($errorInfo, 0);
    }

    public function batchInsert(string $table, string $primaryKey, $columns, $batchSize): string
    {
        $valueClause = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        $valueClauses = implode(', ', array_fill(0, $batchSize, $valueClause));
        $joinedColumns = implode(', ', $columns);
        $sql = "INSERT INTO {$table} ($joinedColumns) VALUES $valueClauses";
        if ($primaryKey) {
            return "{$sql} RETURNING {$primaryKey}";
        }
        return $sql;
    }

    protected function insertEmptyRow(): string
    {
        return "INSERT INTO {$this->query->table} DEFAULT VALUES";
    }

    public function regexpMatcher(): string
    {
        return '~';
    }

    protected function quote(string $word): string
    {
        return "\"{$word}\"";
    }

    public function onConflictUpdate(): string
    {
        $attributes = DialectUtil::buildAttributesPartForUpdate($this->query->updateAttributes);
        $upsertConflictColumns = $this->query->upsertConflictColumns;
        $joinedColumns = implode(', ', $upsertConflictColumns);
        return " ON CONFLICT ({$joinedColumns}) DO UPDATE SET {$attributes}";
    }

    public function onConflictDoNothing(): string
    {
        return " ON CONFLICT DO NOTHING";
    }
}
