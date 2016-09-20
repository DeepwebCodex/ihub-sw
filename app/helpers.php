<?php

if (! function_exists('number_to_string')) {
    /**
     * @param $number
     * @return string
     */
    function number_to_string($number)
    {
        if (!is_numeric($number)) {
            throw new \InvalidArgumentException($number . ' is not number');
        }
        return number_format((float)$number, 2, '.', '');
    }
}
