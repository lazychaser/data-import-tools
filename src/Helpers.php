<?php

namespace Lazychaser\DataImportTools;

class Helpers
{
    /**
     * @param $value
     *
     * @return mixed
     */
    public static function parseInt($value)
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function parseFloat($value)
    {
        if (is_numeric($value)) {
            return $value;
        }

        // Replace decimal separator
        $value = str_replace(',', '.', $value);

        return filter_var($value,
                          FILTER_SANITIZE_NUMBER_FLOAT,
                          FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * @param $value
     *
     * @return int
     */
    public static function isPercents($value)
    {
        return preg_match('/^\d+\s*%$/', $value);
    }
}