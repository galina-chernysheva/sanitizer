<?php

namespace utils\validators\types;

use utils\exceptions\TypeValidationException;
use utils\validators\types\base\StringTypeValidator;

/**
 * Class for phone number (RU) type validation
 * Class RuPhoneNumberTypeValidator
 * @package utils\types
 */
class RuPhoneNumberTypeValidator extends StringTypeValidator
{
    private static $_pattern = "/^[7|8]\d{10}$/";
    protected static $_errorMessage = "Value is not phone number (RU)";

    /**
     * Callback for filter_var
     * @param $value
     * @return mixed|null
     */
    private static function _filterCallback($value) {
        $value = preg_replace("/\p{P}|\s|\+/u", "", $value);
        if (preg_match(self::$_pattern, $value) > 0) {
            $value = preg_replace("/^8(\d{10})$/", "7$1", $value);
            return $value;
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public static function validate($value)
    {
        if (is_null($value)) {
            return $value;
        }

        $value = parent::validate($value);
        $value = filter_var($value, FILTER_CALLBACK, ['options' => 'self::_filterCallback']);
        if (!is_null($value)) {
            return $value;
        }
        throw new TypeValidationException(static::$_errorMessage);
    }
}