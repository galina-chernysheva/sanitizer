<?php

namespace utils\validators\types\base;


use utils\exceptions\TypeValidationException;

/**
 * Class for indexed array type validation
 * Class IndexedArrayTypeValidator
 * @package utils\validators
 */
class IndexedArrayTypeValidator implements ITypeValidator
{
    private static $_errorMessage = "Value is not an indexed array.";

    /**
     * @inheritdoc
     */
    public static function validate($value)
    {
        if (is_null($value)) {
            return $value;
        }

        if (is_array($value) && (empty($value) || array_keys($value) === range(0, count($value) - 1))) {
            return $value;
        }
        throw new TypeValidationException(static::$_errorMessage);
    }
}