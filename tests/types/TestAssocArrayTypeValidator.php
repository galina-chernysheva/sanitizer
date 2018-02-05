<?php

namespace tests\types;


use utils\validators\types\base\AssocArrayTypeValidator;

/**
 * Class for AssocArrayTypeValidator tests
 * Class TestAssocArrayTypeValidator
 * @package tests\types
 */
class TestAssocArrayTypeValidator extends BaseTestTypeValidator
{
    protected static $typeClass = AssocArrayTypeValidator::class;
    private static $_expectedFailMsg = 'Value is not an associative array.';

    /**
     * @inheritdoc
     */
    protected static function assertCondition($resultValue, $expectedValue)
    {
        if (is_object($resultValue) && is_object($expectedValue)) {
            return (array)$resultValue === (array)$expectedValue;
        }
        return $resultValue === $expectedValue;
    }

    /**
     * @inheritdoc
     */
    public static function run()
    {
        print "- AssocArrayTypeValidator\n";

        print "-- 0: NULL\n";
        self::testValidate(null, null);

        print "-- 1: decimal integer as string\n";
        self::testValidate('123', self::$_expectedFailMsg);

        print "-- 2: string\n";
        self::testValidate('123test', self::$_expectedFailMsg);

        print "-- 3: float\n";
        self::testValidate(0.123, self::$_expectedFailMsg);

        print "-- 4: 0\n";
        self::testValidate('0', self::$_expectedFailMsg);

        print "-- 5: phone number _without_ punctuation and spaces\n";
        self::testValidate('+79001234567', self::$_expectedFailMsg);

        print "-- 6: boolean\n";
        self::testValidate(true, self::$_expectedFailMsg);

        print "-- 7: empty array\n";
        self::testValidate([], new \stdClass());

        print "-- 8: associative array\n";
        $expectedValue = new \stdClass();
        $expectedValue->key1 = 'value1';
        $expectedValue->key2 = 'value2';
        self::testValidate(['key1' => 'value1', 'key2' => 'value2'], $expectedValue);

        print "-- 9: indexed array with not ordered keys as range\n";
        $value = [0 => 'value1', 2 => 'value2', 1 => 'value3'];
        self::testValidate($value,  (object)$value);

        print "-- 10: indexed array with ordered keys as range\n";
        self::testValidate([0 => 'value1', 1 => 'value2', 2 => 'value3'], self::$_expectedFailMsg);

        print "-- 11: associative array with numeric keys ordered as range\n";
        self::testValidate(['0' => 'value1', '1' => 'value2', '2' => 'value3'], self::$_expectedFailMsg);

        print "-- 12: object\n";
        $testValue = new \stdClass();
        $testValue->foo = 'bar';
        self::testValidate($testValue, $testValue);

        print "-- 13: empty object\n";
        self::testValidate(new \stdClass(), new \stdClass());

        print "\n";
    }
}