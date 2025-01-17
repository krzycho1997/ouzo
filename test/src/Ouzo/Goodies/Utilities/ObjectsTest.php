<?php
/*
 * Copyright (c) Ouzo contributors, https://github.com/letsdrink/ouzo
 * This file is made available under the MIT License (view the LICENSE file for more information).
 */

use Ouzo\Utilities\Objects;
use PHPUnit\Framework\TestCase;

class ClassImplementingToString
{
    public function __construct(private string $string)
    {
    }

    public function __toString(): string
    {
        return $this->string;
    }
}

class ClassWithProperty
{
    public mixed $property;
    private mixed $privateProperty = 'private value';
}

class ObjectsTest extends TestCase
{
    /**
     * @test
     */
    public function shouldExtractFieldsRecursively()
    {
        //given
        $object = new stdClass();
        $object->field1 = new stdClass();
        $object->field1->field2 = 'value';

        //when
        $result = Objects::getValueRecursively($object, 'field1->field2');

        //then
        $this->assertEquals('value', $result);
    }

    /**
     * @test
     */
    public function getFieldRecursivelyShouldReturnDefaultValueWhenFieldNotFound()
    {
        //given
        $object = new stdClass();

        //when
        $result = Objects::getValueRecursively($object, 'field1->field2', 'default');

        //then
        $this->assertEquals('default', $result);
    }

    /**
     * @test
     */
    public function shouldReturnDefaultForNonExistentProperty()
    {
        //given
        $object = new ClassWithProperty();

        //when
        $result = Objects::getValueRecursively($object, 'NonExistentProperty', 'default');

        //then
        $this->assertEquals('default', $result);
    }

    /**
     * @test
     */
    public function shouldReturnValueForPrivatePropertyWhenFlagIsOn()
    {
        //given
        $object = new ClassWithProperty();

        //when
        $result = Objects::getValueRecursively($object, 'privateProperty', null, true);

        //then
        $this->assertEquals('private value', $result);
    }

    /**
     * @test
     */
    public function shouldReturnNullForPrivatePropertyWhenFlagIsOff()
    {
        //given
        $object = new ClassWithProperty();

        //when
        $result = Objects::getValueRecursively($object, 'privateProperty');

        //then
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function shouldReturnValueOfExistingNotNullProperty()
    {
        //given
        $object = new ClassWithProperty();
        $object->property = 'prop';

        //when
        $result = Objects::getValueRecursively($object, 'property', 'default');

        //then
        $this->assertEquals('prop', $result);
    }

    /**
     * @test
     */
    public function getFieldRecursivelyShouldReturnNullWhenFieldNotFoundAndNoDefaultValueWasSpecified()
    {
        //given
        $object = new stdClass();

        //when
        $result = Objects::getValueRecursively($object, 'field1->field2');

        //then
        $this->assertNull($result);
    }

    /**
     * @test
     */
    public function getFieldRecursivelyShouldReturnNonNestedValue()
    {
        //given
        $object = new stdClass();
        $object->field1 = 'value';

        //when
        $result = Objects::getValueRecursively($object, 'field1');

        //then
        $this->assertEquals('value', $result);
    }

    /**
     * @test
     */
    public function shouldGetFieldNamed0()
    {
        //given
        $object = new stdClass();
        $field = '0';
        $object->$field = 'value';

        //when
        $result = Objects::getValueRecursively($object, '0');

        //then
        $this->assertSame('value', $result);
    }

    /**
     * @test
     */
    public function shouldReturnObjectIfEmptyField()
    {
        //given
        $object = new stdClass();

        //when
        $result = Objects::getValueRecursively($object, '');

        //then
        $this->assertEquals($object, $result);
    }

    /**
     * @test
     */
    public function shouldSetValueRecursivelyForNonNestedField()
    {
        //given
        $object = new stdClass();

        //when
        Objects::setValueRecursively($object, 'field1', 'value');

        //then
        $this->assertEquals('value', $object->field1);
    }

    /**
     * @test
     */
    public function shouldSetValueRecursivelyForNestedField()
    {
        //given
        $object = new stdClass();
        $object->field1 = new stdClass();

        //when
        Objects::setValueRecursively($object, 'field1->field2', 'value');

        //then
        $this->assertEquals('value', $object->field1->field2);
    }

    /**
     * @test
     */
    public function shouldSetValueRecursivelyForNonExistentNestedField()
    {
        //given
        $object = new stdClass();

        //when
        Objects::setValueRecursively($object, 'field1->field2', 'value');

        //then
        $this->assertFalse(isset($object->field1));
    }

    /**
     * @test
     */
    public function shouldStringifyBool()
    {
        //given
        $boolValue = true;

        //when
        $stringifyBool = Objects::toString($boolValue);

        //then
        $this->assertEquals('true', $stringifyBool);
    }

    /**
     * @test
     */
    public function shouldStringifyNull()
    {
        //given
        $null = null;

        //when
        $stringifyNull = Objects::toString($null);

        //then
        $this->assertEquals('null', $stringifyNull);
    }

    /**
     * @test
     */
    public function shouldStringifyString()
    {
        //given
        $string = 'string';

        //when
        $stringifyString = Objects::toString($string);

        //then
        $this->assertEquals('"string"', $stringifyString);
    }

    /**
     * @test
     */
    public function shouldStringifyInt()
    {
        //given
        $int = 1;

        //when
        $stringifyInt = Objects::toString($int);

        //then
        $this->assertEquals('1', $stringifyInt);
    }

    /**
     * @test
     */
    public function shouldStringifyNotAssociativeArray()
    {
        //given
        $notAssociativeArray = ['a', 1];

        //when
        $stringifyArray = Objects::toString($notAssociativeArray);

        //then
        $this->assertEquals('["a", 1]', $stringifyArray);
    }

    /**
     * @test
     */
    public function shouldStringifyAssociativeArray()
    {
        //given
        $associativeArray = ['key' => 'value1', 'key2' => 'value2'];

        //when
        $stringifyArray = Objects::toString($associativeArray);

        //then
        /** @noinspection HtmlUnknownTag */
        $this->assertEquals('[<key> => "value1", <key2> => "value2"]', $stringifyArray);
    }

    /**
     * @test
     */
    public function shouldStringifyObject()
    {
        //given
        $object = new stdClass();
        $object->field1 = 'field1';
        $object->field2 = 'field2';

        //when
        $stringifyObject = Objects::toString($object);

        //then
        /** @noinspection HtmlUnknownTag */
        $this->assertEquals('stdClass {<field1> => "field1", <field2> => "field2"}', $stringifyObject);
    }

    /**
     * @test
     */
    public function shouldStringifyObjectWithToString()
    {
        //given
        $object = new ClassImplementingToString("string");

        //when
        $stringifiedObject = Objects::toString($object);

        //then
        $this->assertEquals('string', $stringifiedObject);
    }

    /**
     * @test
     */
    public function shouldReturnStringWhenNoMatchedTypes()
    {
        //given
        $int = 1;

        //when
        $string = Objects::toString($int);

        //then
        $this->assertSame('1', $string);
    }

    /**
     * @test
     */
    public function shouldReturnValueFormArray()
    {
        //given
        $array = ['id' => 123, 'name' => 'John'];

        //when
        $value = Objects::getValue($array, 'name');

        //then
        $this->assertEquals('John', $value);
    }

    /**
     * @test
     */
    public function shouldReturnValueFormMultidimensionalArray()
    {
        //given
        $array = [
            'id' => 123,
            'name' => 'John',
            'info' => [
                'account' => [
                    'number' => '2343-de',
                    'info' => 'some info about account'
                ]
            ]
        ];

        //when
        $value = Objects::getValueRecursively($array, 'info->account->number');

        //then
        $this->assertEquals('2343-de', $value);
    }

    /**
     * @test
     */
    public function shouldCompareWithNull()
    {
        $this->assertTrue(Objects::equal(null, null));

        $this->assertFalse(Objects::equal(null, 0));
        $this->assertFalse(Objects::equal(null, '0'));
        $this->assertFalse(Objects::equal(null, false));
        $this->assertFalse(Objects::equal(null, 'false'));
        $this->assertFalse(Objects::equal(null, ''));
        $this->assertFalse(Objects::equal(null, []));
        $this->assertFalse(Objects::equal(null, new stdClass()));
    }

    /**
     * @test
     */
    public function shouldCompareWithEmptyString()
    {
        $this->assertTrue(Objects::equal('', ''));

        $this->assertFalse(Objects::equal('', null));
        $this->assertFalse(Objects::equal('', 0));
        $this->assertFalse(Objects::equal('', '0'));
        $this->assertFalse(Objects::equal('', false));
        $this->assertFalse(Objects::equal('', 'false'));
        $this->assertFalse(Objects::equal('', []));
        $this->assertFalse(Objects::equal('', new stdClass()));
    }

    /**
     * @test
     */
    public function shouldCompareStrings()
    {
        $this->assertTrue(Objects::equal('', ''));
        $this->assertTrue(Objects::equal('a', 'a'));
        $this->assertTrue(Objects::equal('1', '1'));

        $this->assertFalse(Objects::equal('a', 'b'));
    }

    /**
     * @test
     */
    public function shouldCompareStringsWithIntegers()
    {
        $this->assertTrue(Objects::equal('1', 1));
        $this->assertTrue(Objects::equal(1, '1'));

        $this->assertFalse(Objects::equal('1', 2));
        $this->assertFalse(Objects::equal(2, '1'));
    }

    /**
     * @test
     */
    public function shouldCompareArrays()
    {
        $this->assertTrue(Objects::equal(['1'], [1]));
        $this->assertTrue(Objects::equal([1], ['1']));
        $this->assertTrue(Objects::equal([null], [null]));
        $this->assertTrue(Objects::equal([''], ['']));
        $this->assertTrue(Objects::equal(['a'], ['a']));
        $this->assertTrue(Objects::equal([new stdClass()], [new stdClass()]));

        $this->assertFalse(Objects::equal(['1'], [2]));
        $this->assertFalse(Objects::equal([2], ['1']));
        $this->assertFalse(Objects::equal([''], [false]));
        $this->assertFalse(Objects::equal([''], [0]));
        $this->assertFalse(Objects::equal([''], [null]));
        $this->assertFalse(Objects::equal(['a'], ['b']));
        $this->assertFalse(Objects::equal([''], [new stdClass()]));
        $this->assertFalse(Objects::equal([null], [new stdClass()]));
        $this->assertFalse(Objects::equal([false], [new stdClass()]));
    }

    /**
     * @test
     */
    public function shouldCompareNestedArrays()
    {
        $array = [
            '1',
            [
                1 => 123
            ],
            new stdClass(),
            3 => null
        ];
        $arrayWithIntConversion = [
            1,
            [
                '1' => '123'
            ],
            new stdClass(),
            3 => null
        ];
        $arrayWithNullToStringConversion = [
            1,
            [
                '1' => '123'
            ],
            new stdClass(),
            3 => ''
        ];
        $arrayWithDifferentKey = [
            1,
            [
                2 => '123'
            ],
            new stdClass()
        ];

        $this->assertTrue(Objects::equal($array, $arrayWithIntConversion));
        $this->assertFalse(Objects::equal($array, $arrayWithNullToStringConversion));
        $this->assertFalse(Objects::equal($array, $arrayWithDifferentKey));
    }

    /**
     * @test
     */
    public function shouldCompareObjects()
    {
        $a = new stdClass();
        $a->var = 1;

        $b = new stdClass();
        $b->var = 2;

        $c = new stdClass();
        $c->var = '1';

        $this->assertTrue(Objects::equal(new stdClass(), new stdClass()));
        $this->assertTrue(Objects::equal($a, $a));
        $this->assertTrue(Objects::equal($a, $c));
        $this->assertFalse(Objects::equal($a, new stdClass()));
    }

    /**
     * @test
     */
    public function shouldHandle0InSelector()
    {
        //given
        $a = new stdClass();
        $a->var = [
            0 => 'a',
            1 => 'b'
        ];

        //when
        $value = Objects::getValueRecursively($a, 'var->0');

        //then
        $this->assertEquals('a', $value);
    }
}
