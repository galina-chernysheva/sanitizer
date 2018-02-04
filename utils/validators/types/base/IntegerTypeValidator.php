<?php

namespace utils\validators\types\base;


use utils\exceptions\TypeValidationException;

/**
 * Class for integer type validation (decimals only)
 * Class IntegerTypeValidator
 * @package utils\types
 */
class IntegerTypeValidator implements ITypeValidator
{
    private static $_errorMessage = "Value is not integer";

    /**
     * @inheritdoc
     */
    public static function validate($value)
    {
        if (is_null($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            $value = filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
            if (!is_null($value)) {
                return $value;
            }
        }
        throw new TypeValidationException(static::$_errorMessage);

    }
}