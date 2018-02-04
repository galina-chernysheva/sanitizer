<?php

namespace utils\validators\types\base;


use utils\exceptions\TypeValidationException;

/**
 * Interface for type validator classes
 * Interface ITypeValidator
 * @package utils\validators\types\base
 */
interface ITypeValidator
{
    /** Validation and conversion of $value
     * @param $value
     * @return mixed
     * @throws TypeValidationException
     */
    public static function validate($value);
}