<?php

namespace utils\validators\types\base;


use utils\exceptions\TypeValidationException;

/**
 * Class for associative array type validation (through stdClass)
 * Class AssocArrayTypeValidator
 * @package utils\validators
 */
class AssocArrayTypeValidator implements ITypeValidator
{
    private static $_errorMessage = "Value is not associative array";

    /**
     * @inheritdoc
     */
    public static function validate($value)
    {
        if (is_null($value)) {
            return $value;
        }

        try {
            $isList = !empty(IndexedArrayTypeValidator::validate($value));
        } catch (TypeValidationException $e) {
            $isList = false;
        }

        if (is_object($value) || (is_array($value) && !$isList)) {
            return (object)$value;
        }
        throw new TypeValidationException(static::$_errorMessage);
    }

}