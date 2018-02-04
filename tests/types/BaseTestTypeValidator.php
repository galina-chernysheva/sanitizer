<?php

namespace tests\types;


use utils\validators\types\base\ITypeValidator;

/**
 * Base class for ITypeValidator unit tests
 * Class BaseTestTypeValidator
 * @package tests\types
 */
abstract class BaseTestTypeValidator
{
    /** @var ITypeValidator */
    protected static $typeClass;
    private static $_failMsgTemplate = "FAIL (expected: %s; got: %s)\n";

    /** Tests execution */
    abstract public static function run();

    /**
     * Success condition of tests
     * @param $resultValue
     * @param $expectedValue
     * @return bool
     */
    protected static function assertCondition($resultValue, $expectedValue)
    {
        return $resultValue === $expectedValue;
    }

    /**
     * Test of ITypeValidator "validate" method
     * @param $testValue
     * @param $expectedValue
     */
    protected static function testValidate($testValue, $expectedValue)
    {
        try {
            $resultValue = (static::$typeClass)::validate($testValue);
        } catch (\Exception $e) {
            $resultValue = $e->getMessage();
        }

        $failMsg = sprintf(self::$_failMsgTemplate, print_r($expectedValue, true), print_r($resultValue, true));
        if (assert(static::assertCondition($resultValue, $expectedValue), $failMsg)) {
            print "--- SUCCESS\n";
        }
    }
}
