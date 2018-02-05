<?php

namespace tests\types;


use utils\validators\types\base\FloatTypeValidator;

/**
 * Class for FloatTypeValidator tests
 * Class TestFloatTypeValidator
 * @package tests\types
 */
class TestFloatTypeValidator extends BaseTestTypeValidator
{
    protected static $typeClass = FloatTypeValidator::class;
    private static $_expectedFailMsg = 'Value is not a float.';

    /**
     * @inheritdoc
     */
    public static function run()
    {
        print "- FloatTypeValidator\n";

        print "-- 0: NULL\n";
        self::testValidate(null, null);        

        print "-- 1: decimal integer as string\n";
        self::testValidate('  123', 123.0);

        print "-- 2: binary integer as string\n";
        self::testValidate('-0b11111110', self::$_expectedFailMsg);

        print "-- 3: octal integer as string\n";
        // todo: fix!!!
        self::testValidate('-0123', self::$_expectedFailMsg);

        print "-- 4: hexadecimal integer as string\n";
        self::testValidate('0x1E', self::$_expectedFailMsg);

        print "-- 5: string\n";
        self::testValidate('123test', self::$_expectedFailMsg);

        print "-- 6: float\n";
        self::testValidate(0.123, 0.123);

        print "-- 7: 0\n";
        self::testValidate('0', 0.0);

        print "-- 8: phone number _without_ punctuation and spaces\n";
        self::testValidate('+790012345678', 790012345678.0);

        print "-- 9: phone number _with_ punctuation and spaces\n";
        self::testValidate('+7(900)12-34-5678', self::$_expectedFailMsg);

        print "-- 10: boolean\n";
        self::testValidate(true, self::$_expectedFailMsg);

        print "-- 12: boolean as string - \"false\"\n";
        self::testValidate('false', self::$_expectedFailMsg);

        print "-- 13: array\n";
        self::testValidate([0, '1', '2' => 3], self::$_expectedFailMsg);

        print "-- 14: object\n";
        $testValue = new \stdClass();
        $testValue->foo = 'bar';
        self::testValidate($testValue, self::$_expectedFailMsg);

        print "-- 15: float scientific notation\n";
        self::testValidate(1.2e+2, 120.0);

        print "-- 16: float with thousands separator\n";
        self::testValidate('1,200.44', 1200.44);

        print "\n";
    }
}