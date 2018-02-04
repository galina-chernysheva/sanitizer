<?php

namespace utils\validators\types\base;


use utils\exceptions\TypeValidationException;

/**
 * Class for string type validation
 * Class StringTypeValidator
 * @package utils\types
 */
class StringTypeValidator implements ITypeValidator
{
    protected static $_errorMessage = "Value is not string";

    /**
     * @inheritdoc
     */
    public static function validate($value)
    {
        if (is_null($value)) {
            return $value;
        }

        if (is_scalar($value) && !is_bool($value)) {
            $value = filter_var($value, FILTER_DEFAULT, FILTER_NULL_ON_FAILURE);
            if (!is_null($value)) {
                return $value;
            }
        }
        throw new TypeValidationException(static::$_errorMessage);
    }
}