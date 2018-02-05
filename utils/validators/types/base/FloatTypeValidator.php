<?php

namespace utils\validators\types\base;


use utils\exceptions\TypeValidationException;

/**
 * Class for float type validation
 * Class FloatTypeValidator
 * @package utils\types
 */
class FloatTypeValidator implements ITypeValidator
{
    private static $_errorMessage = "Value is not a float.";

    /**
     * @inheritdoc
     */
    public static function validate($value)
    {
        if (is_null($value)) {
            return $value;
        }

        // is_float(0) == false и float(0) !== 0, so process zero values separately from filter_var
        // check for equality to concrete values - '0' and 0, because string 'false' must be defined as invalid
        if ($value === 0 || $value === '0') {
            return (float)0;
        }

        // not is_numeric($value) condition, because we should allow comma as thousand separator
        if (is_scalar($value) && !is_bool($value)) {
            // hack: filter_varfor float does not allow hexidecimal and binary formats for int values,
            // but it convert octal integer string to decimal integer, just trim leading 0 (that's weird)
            // so just ignore strings what seem octal integers
            if (!preg_match('|^[+-]0[0-7]+$|', $value)) {
                $value = filter_var(
                    $value, FILTER_VALIDATE_FLOAT,
                    FILTER_FLAG_ALLOW_THOUSAND | FILTER_FLAG_ALLOW_SCIENTIFIC | FILTER_NULL_ON_FAILURE
                );
                if (!is_null($value)) {
                    return $value;
                }
            }
        }
        throw new TypeValidationException(static::$_errorMessage);
    }
}