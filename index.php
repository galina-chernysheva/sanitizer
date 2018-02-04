<?php

use controllers\ValidationController;

require_once "autoload.php";


switch ($_SERVER['QUERY_STRING']) {
    case 'test1':
        $schema = '{"type": "phone"}';
        $customTypes = [];
        break;
    case 'test2':
    case 'test3':
        $schema = '{
            "type": "object",
            "properties": {
                "obj": {
                    "type": "object",
                    "properties": {
                        "objProp1": {
                            "type": "integer"
                        },
                        "objProp2": {
                            "type": "phone"
                        },
                        "objProp3": {
                            "type": "array",
                            "items": {
                                "type": "string"
                            }
                        }
                    }
                },
                "arrFloat": {
                    "type": "array",
                    "items": {
                        "type": "float"
                    }
                },
                "str": {
                    "type": "string"
                },
                "arr": {
                    "type": "array"
                },
                "objInt": {
                    "type": "object",
                    "items": {
                        "type": "integer"
                    }
                }
            }
        }';
        $customTypes = ['phone' => \utils\validators\types\RuPhoneNumberTypeValidator::class];
        break;
    default:
        $schema = '{}';
        $customTypes = [];
}

$controller = new ValidationController();
$controller->process($schema, $customTypes);
