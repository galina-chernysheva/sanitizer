<?php

namespace tests\controller;


use utils\validators\types\RuPhoneNumberTypeValidator;

/**
 * Class for ValidationController tests
 * Class TestValidationController
 * @package tests\controller
 */
class TestValidationController
{
    private static $_dataDir = __DIR__ . DIRECTORY_SEPARATOR . '_testData';
    private static $_failMsgTemplate = "FAIL (expected: (%d) %s; got: (%d) %s)\n";

    /**
     * Test of ValidationController "process" method
     * @param int $testNum
     * @param int $expectedResponseStatus
     */
    protected static function testValidate(int $testNum, int $expectedResponseStatus) {
        $rawData =
            file_get_contents(self::$_dataDir . DIRECTORY_SEPARATOR . $testNum . '.data.json');
        $expectedResult = json_encode(json_decode(
            file_get_contents(self::$_dataDir . DIRECTORY_SEPARATOR . $testNum . '.result.json'),
            true
        ));

        try {
            $ch = curl_init('http://' . HOST_NAME . '/?test' . $testNum);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json'
                ],
                CURLOPT_POSTFIELDS => $rawData
            ]);
            $testResult = curl_exec($ch);
            $testResponseStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        } catch (\Throwable $e) {
            $testResult = $e->getMessage();
            $testResponseStatus = null;
        }

        $failMsg = sprintf(
            self::$_failMsgTemplate,
            $expectedResponseStatus, print_r($expectedResult, true),
            $testResponseStatus, print_r($testResult, true)
        );
        if (assert($testResult === $expectedResult && $testResponseStatus === $expectedResponseStatus, $failMsg)) {
            print "--- SUCCESS\n";
        }
    }

    /** Tests execution */
    public static function run()
    {
        print "- ValidationController\n";

        print "-- 1: not allowed type in schema (not base and not defined as custom)\n";
        self::testValidate(1, 500);

        print "-- 2: invalid data\n";
        self::testValidate(2, 200);

        print "-- 3: valid data\n";
        self::testValidate(3, 200);

        print "\n";
    }
}