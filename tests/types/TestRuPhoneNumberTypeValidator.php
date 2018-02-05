<?php

namespace tests\types;


use utils\validators\types\RuPhoneNumberTypeValidator;

/**
 * Class for RuPhoneNumberTypeValidator tests
 * Class TestRuPhoneNumberTypeValidator
 * @package tests\types
 */
class TestRuPhoneNumberTypeValidator extends BaseTestTypeValidator
{
    protected static $typeClass = RuPhoneNumberTypeValidator::class;
    private static $_expectedFailMsg = 'Value is not a phone number (RU).';

    /**
     * @inheritdoc
     */
    public static function run()
    {
        print "- RuPhoneNumberTypeValidator\n";

        print "-- 0: NULL\n";
        self::testValidate(null, null);

        print "-- 1: decimal integer as string\n";
        self::testValidate('123', self::$_expectedFailMsg);

        print "-- 2: binary integer as string\n";
        self::testValidate('-0b11111110', self::$_expectedFailMsg);

        print "-- 3: octal integer as string\n";
        self::testValidate('-0123', self::$_expectedFailMsg);

        print "-- 4: string\n";
        self::testValidate('123test', self::$_expectedFailMsg);

        print "-- 5: float\n";
        self::testValidate(0.123, self::$_expectedFailMsg);

        print "-- 6: phone number _without_ punctuation and spaces\n";
        self::testValidate('+79001234567', '79001234567');

        print "-- 7: phone number _with_ punctuation and spaces\n";
        self::testValidate(' +7 (900) 12 - 34 --- 567', '79001234567');

        print "-- 8: boolean\n";
        self::testValidate(true, self::$_expectedFailMsg);

        print "-- 9: array\n";
        self::testValidate([0, '1', '2' => 3], self::$_expectedFailMsg);

        print "-- 10: object\n";
        $testValue = new \stdClass();
        $testValue->foo = 'bar';
        self::testValidate($testValue, self::$_expectedFailMsg);

        print "-- 11: phone number with missed digits\n";
        self::testValidate('+7(900)123 -45-6', self::$_expectedFailMsg);

        print "-- 12: phone number with extra digits\n";
        self::testValidate('+7(900)123 -45-67-8', self::$_expectedFailMsg);

        print "-- 13: phone number starting with 8\n";
        self::testValidate('8(900)12-34-567', '79001234567');

        print "-- 14: short phone number\n";
        self::testValidate('244-55-66', self::$_expectedFailMsg);

        print "-- 15: phone number starting with not 7 or 8\n";
        self::testValidate('9(900)12-34-567', self::$_expectedFailMsg);

        print "-- 15: phone number and something else\n";
        self::testValidate('+8(900)12-34-567 something else', self::$_expectedFailMsg);

        print "-- 16: empty string\n";
        self::testValidate('', self::$_expectedFailMsg);

        print "\n";
    }
}