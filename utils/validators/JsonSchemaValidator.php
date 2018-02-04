<?php

namespace utils\validators;


use utils\exceptions\JsonSchemaValidationException;
use utils\exceptions\TypeValidationException;
use utils\validators\types\base as baseTypes;

/**
 * Json data validation using schema of data types
 * Class JsonSchemaValidator
 * @package utils
 */
class JsonSchemaValidator
{
    private static $baseTypesMap = [
        'integer'   => baseTypes\IntegerTypeValidator::class,
        'float'     => baseTypes\FloatTypeValidator::class,
        'string'    => baseTypes\StringTypeValidator::class,
        'array'     => baseTypes\IndexedArrayTypeValidator::class,
        'object'    => baseTypes\AssocArrayTypeValidator::class
    ];

    /** @var  array */
    private $_typesMap;

    /**
     * Set allowing types for validation
     * JsonSchemaValidator constructor.
     * @param array|null $customTypesMap
     */
    public function __construct(array $customTypesMap = null)
    {
        $this->_typesMap = self::$baseTypesMap;
        if (!empty($customTypesMap)) {
            $this->_typesMap = array_merge($this->_typesMap, $customTypesMap);
        }
    }

    /**
     * Validation json string $rawData according $schema which describes data structure and types of values
     *
     * schema sample:
     * [
     *      'type' => 'object',
     *      'properties': [
     *          'obj' => [                      // this is associative array with known keys and known its types
     *              'type' => 'object',
     *              'properties' => [
     *                  'objProp1' => [
     *                      'type' => 'integer'
     *                  ],
     *                  'objProp2' => [
     *                      'type' => 'phone'
     *                  ],
     *                  'objProp3' => [         // this property type is array of strings
     *                      'type' => 'array',
     *                      'items' => [
     *                          'type' => 'string'
     *                      ]
     *                  ]
     *              ]
     *          ],
     *          'arrFloat' => [                 // this param type is array of floats
     *              'type' => 'array'
     *              'items' => [
     *                  'type' => 'float'
     *              ]
     *          ],
     *          'str' => [
     *              'type' => 'string'
     *          ],
     *          'arr' => [                      // we know that this param type is array, but we don't know or don't care which type it's values of
     *              'type' => 'array',
     *          ],
     *          'objInt' => [                   // this is associative array with unknown keys (missed "properties"), but known type of all it's values
     *              'type' => 'object',
     *              'items' => [
     *                  'type' => 'integer'
     *              ]
     *          ]
     *      ]
     * ]
     *
     * json data sample for the above schema:
     * {
     *      'obj': {
                'objProp1': 111,
     *          'objProp2': '+7 (900) 123 45 67',
     *          'objProp3': ['this', 'is', 'array', 'of', 'strings']
     *      },
     *      'arrFloat': [1.1, -2.22, '3.33', '1e7'],
     *      'str': 'just single string',
     *      'arr': ['no', 'matter', 'what', 'is', 'in', 1, ['hello!'], {'even': 'object'}],
     *      'objInt': {
     *          '2018-01-31': -18,
     *          '2018-02-01': -17,
     *          '2018-02-02': -12
     *      }
     * }
     *
     * @param string $schema
     * @param string $rawData
     * @return array
     * @throws JsonSchemaValidationException
     */
    public function validate(string $schema, string $rawData): array
    {
        $errors = [];

        $data = json_decode($rawData);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonSchemaValidationException('Invalid JSON data: ' . json_last_error_msg());
        }
        $schema = json_decode($schema, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonSchemaValidationException('Invalid JSON schema: ' . json_last_error_msg());
        }

        if (!empty((array)$data)) {
            if (empty($schema)) {
                throw new JsonSchemaValidationException('JSON data schema is not defined');
            }

            $this->_validateType($schema, $data, $errors);
        }

        return [
            'errors' => $errors,
            'data' => is_object($data) ? (array)$data : $data
        ];
    }

    /**
     * Type validation of $value
     * @param array $schema
     * @param $value
     * @param array $errors
     * @param string|null $paramName
     * @throws JsonSchemaValidationException
     */
    private function _validateType(array $schema, &$value, array &$errors, string $paramName = null)
    {
        $typeName = $schema['type'] ?? null;
        if (is_null($typeName)) {
            throw new JsonSchemaValidationException(
                (!is_null($paramName) ? "{$paramName}: " : '')
                    . 'Type of parameter if not defined ("type" key in parameter type description)'
            );
        }
        if (!array_key_exists($typeName, $this->_typesMap)) {
            throw new JsonSchemaValidationException(
                (!is_null($paramName) ? "{$paramName}: " : '') . "Type \"{$typeName}\" is not supported"
            );
        }

        try {
            /** @var baseTypes\ITypeValidator $type */
            $typeClass = $this->_typesMap[$typeName];
            $type = new $typeClass();
            $value = $type->validate($value);

            switch ($typeName) {
                case 'array':
                    $this->_validateList($schema, $value, $errors, $paramName);
                    break;
                case 'object':
                    $this->_validateObject($schema, $value, $errors, $paramName);
                    // convert to array for more convenient usage as result of validation
                    if (!is_null($value)) {
                        $value = (array)$value;
                    }
                    break;
                default:
                    break;
            }
        } catch (TypeValidationException $e) {
            $errors[] = (!is_null($paramName) ? "{$paramName}: " : '') . $e->getMessage();
        }
    }

    /**
     * Validation of array values
     * @param array $schema
     * @param $value
     * @param array $errors
     * @param string|null $paramName
     * @throws JsonSchemaValidationException
     */
    private function _validateList(array $schema, &$value, array &$errors, string $paramName = null)
    {
        if (empty($value)) {
            return;
        }

        $itemsDescr = $schema['items'] ?? null;
        if (empty($itemsDescr)) {
            // we able to omit type description for values, just check that all of its together is array
            // convert to array for more convenient usage as result of validation (objects may be inside)
            $value = json_decode(json_encode($value), true);
            return;
        }

        if (!is_array($itemsDescr)) {
            throw new JsonSchemaValidationException(
                (!is_null($paramName) ? "{$paramName}: " : '') .
                'Type description (schema) of values must be array of form [type => .., properties?: {..}, items? => [..]]'
            );
        }

        foreach ($value as $idx => &$itemValue) {
            $idxParamName = !is_null($paramName) ? "{$paramName}[{$idx}]" : $idx;
            $this->_validateType($itemsDescr, $itemValue, $errors, $idxParamName);
        }
    }

    /**
     * Validation of object properties values
     * @param array $schema
     * @param \stdClass|null $value
     * @param array $errors
     * @param string|null $paramName
     * @throws JsonSchemaValidationException
     */
    private function _validateObject(array $schema, &$value, array &$errors, string $paramName = null)
    {
        if (empty((array)$value)) {
            return;
        }

        $propertiesDescr = $schema['properties'] ?? null;
        $itemsDescr = $schema['items'] ?? null;

        if (empty($itemsDescr) && empty($propertiesDescr)) {
            // we able to omit both declarations of type description:
            // no matter which properties and which types values of in this object
            // convert to array for more convenient usage as result of validation (objects may be inside)
            $value = json_decode(json_encode($value), true);
            return;
        }

        // if defined "items" then we don't care which properties object has,
        // just check that all of its values is defined type of
        if (!empty($itemsDescr)) {
            if (!is_array($itemsDescr)) {
                throw new JsonSchemaValidationException(
                    (!is_null($paramName) ? "{$paramName}: " : '') .
                    'Type description (schema) of properties values must be array of form [type => .., properties?: {..}, items? => [..]]'
                );
            }

            foreach ($value as $propName => $propValue) {
                if (!is_null($paramName)) {
                    $propName = "{$paramName}[{$propName}]";
                }
                $this->_validateType($itemsDescr, $propValue, $errors, $propName);
            }
        }
        // if defined "properties" then validate each defined property type according property type schema
        else {
            foreach ($propertiesDescr as $propName => $propSchema) {
                if (!property_exists($value, $propName)) {
                    $errors[] = "{$paramName}: Property \"{$propName}\" is not defined";
                    continue;
                }
                $propParamName = !is_null($paramName) ? "{$paramName}[{$propName}]" : $propName;
                if (!is_array($propSchema)) {
                    throw new JsonSchemaValidationException(
                        "{$propParamName}: " .
                        'Type description (schema) of property value must be array of form [type => .., properties?: {..}, items? => [..]]'
                    );
                }
                $this->_validateType($propSchema, $value->$propName, $errors, $propParamName);
            }
        }
    }
}