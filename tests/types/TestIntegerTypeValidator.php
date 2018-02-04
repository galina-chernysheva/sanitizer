<?php

namespace tests\types;


use utils\validators\types\base\IntegerTypeValidator;

/**
 * Class for IntegerTypeValidator tests
 * Class TestIntegerTypeValidator
 * @package tests\types
 */
class TestIntegerTypeValidator extends BaseTestTypeValidator
{
    protected static $typeClass = IntegerTypeValidator::class;
    private static $_expectedFailMsg = 'Value is not integer';

    /**
     * @inheritdoc
     */
    public static function run()
    {
        print "- IntegerTypeValidator\n";

        print "-- 0: NULL\n";
        self::testValidate(null, null);

        print "-- 1: decimal integer as string\n";
        self::testValidate('  123', 123);

        print "-- 2: binary integer as string\n";
        self::testValidate('  -0b11111110', self::$_expectedFailMsg);

        print "-- 3: octal integer as string\n";
        self::testValidate('-0123', self::$_expectedFailMsg);

        print "-- 4: hexadecimal integer as string\n";
        self::testValidate('0x1E', self::$_expectedFailMsg);

        print "-- 5: string\n";
        self::testValidate('123test', self::$_expectedFailMsg);

        print "-- 6: float\n";
        self::testValidate(0.123, self::$_expectedFailMsg);

        print "-- 7: number out of boundaries\n";
        self::testValidate(pow(10, 19), self::$_expectedFailMsg);

        print "-- 8: 0\n";
        self::testValidate('0', 0);

        print "-- 9: phone number _without_ punctuation and spaces\n";
        self::testValidate('+79001234567', 79001234567);

        print "-- 10: phone number _with_ punctuation and spaces\n";
        self::testValidate('+7(900)12-34-567', self::$_expectedFailMsg);

        print "-- 11: boolean\n";
        self::testValidate(true, self::$_expectedFailMsg);

        print "-- 12: boolean as string\n";
        self::testValidate('true', self::$_expectedFailMsg);

        print "-- 13: array\n";
        self::testValidate([0, '1', '2' => 3], self::$_expectedFailMsg);

        print "-- 14: object\n";
        $testValue = new \stdClass();
        $testValue->foo = 'bar';
        self::testValidate($testValue, self::$_expectedFailMsg);

        print "\n";
    }
}