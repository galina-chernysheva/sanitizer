<?php

namespace tests\types;


use utils\validators\types\base\StringTypeValidator;

/**
 * Class for StringTypeValidator tests
 * Class TestStringTypeValidator
 * @package tests\types
 */
class TestStringTypeValidator extends BaseTestTypeValidator
{
    protected static $typeClass = StringTypeValidator::class;
    private static $_expectedFailMsg = 'Value is not string';

    /**
     * @inheritdoc
     */
    public static function run()
    {
        print "- StringTypeValidator\n";

        print "-- 0: NULL\n";
        self::testValidate(null, null);

        print "-- 1: decimal integer as string\n";
        self::testValidate('123', '123');

        print "-- 2: binary integer as string\n";
        self::testValidate('-0b11111110', '-0b11111110');

        print "-- 3: octal integer as string\n";
        self::testValidate('-0123', '-0123');

        print "-- 4: hexadecimal integer as string\n";
        self::testValidate('0x1E', '0x1E');

        print "-- 5: string\n";
        self::testValidate('123test', '123test');

        print "-- 6: float\n";
        self::testValidate(0.123, '0.123');

        print "-- 7: phone number _without_ punctuation and spaces\n";
        self::testValidate('+79001234567', '+79001234567');

        print "-- 8: boolean\n";
        self::testValidate(true, self::$_expectedFailMsg);

        print "-- 9: array\n";
        self::testValidate([0, '1', '2' => 3], self::$_expectedFailMsg);

        print "-- 10: object\n";
        $testValue = new \stdClass();
        $testValue->foo = 'bar';
        self::testValidate($testValue, self::$_expectedFailMsg);

        print "-- 11: empty string\n";
        self::testValidate('', '');

        print "\n";
    }
}