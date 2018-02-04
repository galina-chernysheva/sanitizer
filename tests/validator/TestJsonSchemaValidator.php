<?php

namespace tests\validator;


use utils\validators\JsonSchemaValidator;
use utils\validators\types\RuPhoneNumberTypeValidator;

/**
 * Class for JsonSchemaValidator tests
 * Class TestJsonSchemaValidator
 * @package tests\validators
 */
class TestJsonSchemaValidator
{
    private static $_dataDir = __DIR__ . DIRECTORY_SEPARATOR . '_testData';
    private static $_failMsgTemplate = "FAIL (expected: %s; got: %s)\n";

    /**
     * Test of JsonSchemaValidator "validate" method
     * @param int $testNum
     * @param array $customTypesMap
     */
    private static function testValidate(int $testNum, array $customTypesMap = []) {
        $rawData = file_get_contents(self::$_dataDir . DIRECTORY_SEPARATOR . $testNum . '.data.json');
        $schema = file_get_contents(self::$_dataDir . DIRECTORY_SEPARATOR . $testNum . '.schema.json');
        $expectedResult = json_decode(
            file_get_contents(self::$_dataDir . DIRECTORY_SEPARATOR . $testNum . '.result.json'),
            true
        );

        try {
            $validator = new JsonSchemaValidator($customTypesMap);
            $testResult = $validator->validate($schema, $rawData);
        } catch (\Throwable $e) {
            $testResult = $e->getMessage();
        }

        $failMsg = sprintf(self::$_failMsgTemplate, print_r($expectedResult, true), print_r($testResult, true));
        if (assert($testResult === $expectedResult, $failMsg)) {
            print "--- SUCCESS\n";
        }
    }

    /** Tests execution */
    public static function run()
    {
        print "- JsonSchemaValidator\n";

        $customTypesMap = [
            'phone' => RuPhoneNumberTypeValidator::class
        ];

        print "-- 1: data as invalid json\n";
        self::testValidate(1, []);

        print "-- 2: empty schema\n";
        self::testValidate(2, []);

        print "-- 3: empty schema and data\n";
        self::testValidate(3, []);

        print "-- 4: not allowed type in schema (not base and not defined as custom)\n";
        self::testValidate(4, []);

        print "-- 5: not defined type of value in schema\n";
        self::testValidate(5, []);

        print "-- 6: valid data and schema\n";
        self::testValidate(6, $customTypesMap);

        print "-- 7: invalid schema\n";
        self::testValidate(7, $customTypesMap);

        print "-- 8: valid scalar data\n";
        self::testValidate(8, $customTypesMap);

        print "-- 9: valid array data\n";
        self::testValidate(9, $customTypesMap);

        print "-- 10: another valid data\n";
        self::testValidate(10, $customTypesMap);

        print "-- 11: invalid data\n";
        self::testValidate(11, $customTypesMap);

        print "\n";
    }
}