<?php

use \tests\types as tTests;
use \tests\validator as vTests;
use \tests\controller as cTests;

require_once "autoload.php";

print "Tests for types validators\n";
tTests\TestIntegerTypeValidator::run();
tTests\TestFloatTypeValidator::run();
tTests\TestStringTypeValidator::run();
tTests\TestRuPhoneNumberTypeValidator::run();
tTests\TestIndexedArrayTypeValidator::run();
tTests\TestAssocArrayTypeValidator::run();

print "\n\n";

print "Tests for json schema validator\n";
vTests\TestJsonSchemaValidator::run();

print "\n\n";

print "Tests for controller\n";
cTests\TestValidationController::run();
