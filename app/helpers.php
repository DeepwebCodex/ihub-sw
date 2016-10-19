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

if (! function_exists('get_formatted_date')) {
    /**
     * @param $value
     * @param string $format
     * @return false|string
     */
    function get_formatted_date($value, $format = 'Y-m-d H:i:s')
    {
        if (is_numeric($value)) {
            $date = date_create_from_format('U', $value);
        } else {
            $date = date_create_from_format('Y-m-d H:i:s', $value);
        }
        if ($date && $date = date_format($date, $format)) {
            return $date;
        }
        throw new \InvalidArgumentException;
    }
}

if (! function_exists('integration_config')) {

    /**
     * @param \Illuminate\Foundation\Application $app
     * @param string $environment
     */
    function integration_config(\Illuminate\Foundation\Application $app, $environment){
        if($environment && $app){
            $basePath = $app->basePath().DIRECTORY_SEPARATOR.'integrations'.DIRECTORY_SEPARATOR;
            $environmentConfig = $basePath . $environment . '.php';

            if(!file_exists($environmentConfig)){
                $environmentConfig = $basePath . 'default.php';
            }

            $integrations = require $environmentConfig;

            Illuminate\Container\Container::getInstance()->make('config')->set('integrations', $integrations);
        }
    }

}
