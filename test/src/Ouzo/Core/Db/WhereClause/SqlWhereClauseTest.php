<?php
/*
 * Copyright (c) Ouzo contributors, https://github.com/letsdrink/ouzo
 * This file is made available under the MIT License (view the LICENSE file for more information).
 */
namespace Ouzo\Db\WhereClause;


use PHPUnit\Framework\TestCase;

class SqlWhereClauseTest extends TestCase
{
    /**
     * @test
     */
    public function shouldAcceptSingleValueAsParams()
    {
        // when
        $result = WhereClause::create('name = ?', 'bob');

        // then
        $this->assertEquals(['bob'], $result->getParameters());
        $this->assertEquals('name = ?', $result->toSql());
    }

    /**
     * @test
     */
    public function shouldWrapSqlWithOrInParenthesis()
    {
        // when
        $result = WhereClause::create('name = ? OR name = ?', ['bob', 'john']);

        // then
        $this->assertEquals(['bob', 'john'], $result->getParameters());
        $this->assertEquals('(name = ? OR name = ?)', $result->toSql());
    }

    /**
     * @test
     */
    public function shouldAcceptSingleNullValueAsParam()
    {
        // when
        $result = WhereClause::create('name = ?', null);

        // then
        $this->assertEquals([null], $result->getParameters());
        $this->assertEquals('name = ?', $result->toSql());
    }
}