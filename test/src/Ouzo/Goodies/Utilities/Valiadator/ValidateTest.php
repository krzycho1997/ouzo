<?php
/*
 * Copyright (c) Ouzo contributors, https://github.com/letsdrink/ouzo
 * This file is made available under the MIT License (view the LICENSE file for more information).
 */

use Ouzo\Tests\CatchException;
use Ouzo\Utilities\Validator\Validate;
use Ouzo\Utilities\Validator\ValidateException;
use PHPUnit\Framework\TestCase;

class ValidateTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnTrueWhenIsTrue()
    {
        //given
        $value = 'text';

        //when
        $isTrue = Validate::isTrue(strlen($value) == 4, 'Length is not correct');

        //then
        $this->assertTrue($isTrue);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenIsNotEmail()
    {
        //given
        $value = 'some_bad_email';

        //when
        CatchException::when(new Validate())->isEmail($value, 'Is not correct email');

        //then
        CatchException::assertThat()->isInstanceOf(ValidateException::class);
        CatchException::assertThat()->hasMessage('Is not correct email');
    }

    /**
     * @test
     */
    public function shouldReturnTrueWhenIsCorrectEmail()
    {
        //given
        $value = 'foo.bar@example.pl';

        //when
        $isEmail = Validate::isEmail($value, 'Is not correct email');

        //then
        $this->assertTrue($isEmail);
    }

    /**
     * @test
     */
    public function shouldThrowExceptionWhenIsNull()
    {
        //given
        $value = null;

        //when
        CatchException::when(new Validate())->isNotNull($value, 'Is null');

        //then
        CatchException::assertThat()->isInstanceOf(ValidateException::class);
        CatchException::assertThat()->hasMessage('Is null');
    }

    /**
     * @test
     */
    public function shouldReturnTrueWhenIsNotNull()
    {
        //given
        $value = 'not null';

        //when
        $isNotNull = Validate::isNotNull($value, 'Is null');

        //then
        $this->assertTrue($isNotNull);
    }
}
