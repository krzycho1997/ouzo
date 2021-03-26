<?php
/*
 * Copyright (c) Ouzo contributors, https://github.com/letsdrink/ouzo
 * This file is made available under the MIT License (view the LICENSE file for more information).
 */

use Ouzo\Tests\Assert;
use Ouzo\Validatable;
use PHPUnit\Framework\TestCase;

class ValidatableChild extends Validatable
{
    public function __construct(array $errors = [], array $errorFields = [])
    {
        $this->errors($errors);
        $this->errorFields = $errorFields;
    }

    public function validate(): void
    {
        //do not reset errors
    }
}

class ValidatableParent extends Validatable
{
    public function __construct(private Validatable $child, array $errors = [], array $errorFields = [])
    {
        $this->errors($errors);
        $this->errorFields = $errorFields;
    }

    public function validate(): void
    {
        $this->validateAssociated($this->child);
    }
}

class ValidatableTest extends TestCase
{
    /**
     * @test
     */
    public function shouldReturnParentAndChildErrors()
    {
        //given
        $child = new ValidatableChild(['error from child'], ['errorField from child']);
        $validatable = new ValidatableParent($child, ['error from parent'], ['errorField from parent']);

        //when
        $validatable->validate();

        //then
        $this->assertEquals(['error from parent', 'error from child'], $validatable->getErrors());
    }

    /**
     * @test
     */
    public function shouldReturnParentAndChildErrorFields()
    {
        //given
        $child = new ValidatableChild(['error from child'], ['errorField from child']);
        $validatable = new ValidatableParent($child, ['error from parent'], ['errorField from parent']);

        //when
        $validatable->validate();

        //then
        $this->assertEquals(['errorField from parent', 'errorField from child'], $validatable->getErrorFields());
    }

    /**
     * @test
     */
    public function shouldNotValidIfBothParentAndChildValid()
    {
        $child = new ValidatableChild();
        $validatable = new ValidatableParent($child);

        $this->assertTrue($validatable->isValid());
    }

    /**
     * @test
     */
    public function shouldNotBeValidIfChildNotValid()
    {
        $child = new ValidatableChild(['error from child'], ['errorField from child']);
        $validatable = new ValidatableParent($child);

        $this->assertFalse($validatable->isValid());
    }

    /**
     * @test
     */
    public function shouldNotBeValidIfParentNotValid()
    {
        $child = new ValidatableChild();
        $validatable = new ValidatableParent($child, ['error from parent'], ['errorField from parent']);

        $this->assertFalse($validatable->isValid());
    }

    /**
     * @test
     */
    public function shouldValidateWrongDateTime()
    {
        // given
        $validatable = new Validatable();

        // when
        $validatable->validateDateTime('wrong-date-format', 'Wrong date.');

        // then
        $errors = $validatable->getErrors();
        $this->assertCount(1, $errors);
        $this->assertEquals('Wrong date.', $errors[0]);
    }

    /**
     * @test
     */
    public function shouldValidateCorrectDateTime()
    {
        // given
        $validatable = new Validatable();

        // when
        $validatable->validateDateTime('2011-02-05', 'Wrong date.');

        // then
        $this->assertEmpty($validatable->getErrors());
    }

    /**
     * @test
     */
    public function shouldClearErrorBeforeValidation()
    {
        // given
        $validatable = new Validatable();
        $validatable->validateAssociated(new ValidatableChild(['error'], ['errorField']));

        // when
        $validatable->validate();

        // then
        $this->assertEmpty($validatable->getErrors());
        $this->assertEmpty($validatable->getErrorFields());
    }

    /**
     * @test
     */
    public function shouldValidStringMaxLength()
    {
        //given
        $validatable = new Validatable();

        //when
        $validatable->validateStringMaxLength('string suits', 30, 'Too long string');

        //then
        $errors = $validatable->getErrors();
        $this->assertCount(0, $errors);
    }

    /**
     * @test
     */
    public function shouldValidateStringMaxLengthWhenLengthEqualsMax()
    {
        //given
        $validatable = new Validatable();

        //when
        $validatable->validateStringMaxLength('123', 3, 'Too long string');

        //then
        $errors = $validatable->getErrors();
        $this->assertCount(0, $errors);
    }

    /**
     * @test
     */
    public function shouldValidateStringMaxLengthWhenLengthEqualsMaxPlusOne()
    {
        //given
        $validatable = new Validatable();

        //when
        $validatable->validateStringMaxLength('1234', 3, 'Too long string');

        //then
        $errors = $validatable->getErrors();
        $this->assertEquals('Too long string', $errors[0]);
    }

    /**
     * @test
     */
    public function shouldNotBeValidStringMaxLength()
    {
        //given
        $validatable = new Validatable();

        //when
        $validatable->validateStringMaxLength('string is too long', 3, 'Too long string');

        //then
        $errors = $validatable->getErrors();
        $this->assertEquals('Too long string', $errors[0]);
    }

    /**
     * @test
     */
    public function shouldAddErrorIfValueIsNotTrue()
    {
        //given
        $validatable = new Validatable();

        //when
        $validatable->validateTrue(false, 'error', 'field');

        //then
        Assert::thatArray($validatable->getErrors())->containsOnly('error');
        Assert::thatArray($validatable->getErrorFields())->containsOnly('field');
    }

    /**
     * @test
     */
    public function shouldAddErrorIfValueIsBlank()
    {
        //given
        $validatable = new Validatable();

        //when
        $validatable->validateNotBlank('', 'blank', 'field');

        //then
        Assert::thatArray($validatable->getErrors())->containsOnly('blank');
        Assert::thatArray($validatable->getErrorFields())->containsOnly('field');
    }

    /**
     * @test
     */
    public function shouldNotTreatZeroAsBlank()
    {
        //given
        $validatable = new Validatable();

        //when
        $validatable->validateNotBlank('0', 'blank');

        //then
        Assert::thatArray($validatable->getErrors())->isEmpty();
        Assert::thatArray($validatable->getErrorFields())->isEmpty();
    }

    /**
     * @test
     */
    public function shouldValidateAssociatedCollection()
    {
        // given
        $validatable = new Validatable();
        $others = [
            new ValidatableChild(['error1'], ['errorField1']),
            new ValidatableChild(['error2'], ['errorField2'])
        ];

        // when
        $validatable->validateAssociatedCollection($others);

        // then
        Assert::thatArray($validatable->getErrors())->containsOnly('error1', 'error2');
        Assert::thatArray($validatable->getErrorFields())->containsOnly('errorField1', 'errorField2');
    }

    /**
     * @test
     */
    public function notEmptyShouldReturnErrorForEmpty()
    {
        //given
        $object = '';
        $validatable = new Validatable();

        //when
        $validatable->validateNotEmpty($object, 'error1');

        //then
        Assert::thatArray($validatable->getErrors())->containsOnly('error1');
    }

    /**
     * @test
     */
    public function notEmptyShouldNotReturnErrorForNotEmpty()
    {
        //given
        $object = new stdClass();
        $validatable = new Validatable();

        //when
        $validatable->validateNotEmpty($object, 'error1');

        //then
        $this->assertEmpty($validatable->getErrors());
    }

    /**
     * @test
     */
    public function emptyShouldReturnErrorForNonEmpty()
    {
        //given
        $object = new stdClass();
        $validatable = new Validatable();

        //when
        $validatable->validateEmpty($object, 'error1');

        //then
        Assert::thatArray($validatable->getErrors())->containsOnly('error1');
    }

    /**
     * @test
     */
    public function emptyShouldNotReturnErrorForEmpty()
    {
        //given
        $object = '';
        $validatable = new Validatable();

        //when
        $validatable->validateEmpty($object, 'error1');

        //then
        $this->assertEmpty($validatable->getErrors());
    }
}
