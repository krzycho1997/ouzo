<?php
/*
 * Copyright (c) Ouzo contributors, https://github.com/letsdrink/ouzo
 * This file is made available under the MIT License (view the LICENSE file for more information).
 */

namespace Ouzo\Utilities\Iterator;

use ArrayIterator;
use PHPUnit\Framework\TestCase;

class BatchingIteratorTest extends TestCase
{
    /**
     * @test
     */
    public function shouldChunkElementsWhenLengthDivisibleByChunk()
    {
        //given
        $array = [1, 2, 3, 4];
        $batchIterator = new BatchingIterator(new ArrayIterator($array), 2);
        $result = [];

        //when
        foreach ($batchIterator as $key => $value) {
            $result[$key] = $value;
        }

        //then
        $this->assertEquals([[1, 2], [3, 4]], $result);
    }

    /**
     * @test
     */
    public function shouldChunkElementsWhenLengthNotDivisibleByChunk()
    {
        //given
        $array = [1, 2, 3];
        $batchIterator = new BatchingIterator(new ArrayIterator($array), 2);
        $result = [];

        //when
        foreach ($batchIterator as $key => $value) {
            $result[$key] = $value;
        }

        //then
        $this->assertEquals([[1, 2], [3]], $result);
    }

    /**
     * @test
     */
    public function shouldNotBeValidForEmptyArray()
    {
        //given
        $batchIterator = new BatchingIterator(new ArrayIterator([]), 2);

        //when
        $valid = $batchIterator->valid();

        //then
        $this->assertFalse($valid);
    }

    /**
     * @test
     */
    public function shouldRewindIterator()
    {
        $ait = new ArrayIterator(['a', 'b', 'c', 'd']);
        $ait->next();
        $ait->next();
        $batchIterator = new BatchingIterator($ait, 2);

        //when
        $batchIterator->rewind();

        //then
        $this->assertEquals([['a', 'b'], ['c', 'd']], iterator_to_array($batchIterator));
    }

    /**
     * @test
     */
    public function shouldPreserveKeysInChunk()
    {
        //given
        $array = [1, 2, 3, 4];
        $batchIterator = new BatchingIterator(new ArrayIterator($array), 2, BatchingIterator:: OPTION_PRESERVER_KEYS);
        $result = [];

        //when
        foreach ($batchIterator as $key => $value) {
            $result[$key] = $value;
        }

        //then
        $this->assertEquals([[1, 2], [2 => 3, 3 => 4]], $result);
    }
}
