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
    function integration_config(\Illuminate\Foundation\Application $app, $environment)
    {
        if ($environment && $app) {
            $basePath = $app->basePath() . DIRECTORY_SEPARATOR . 'integrations' . DIRECTORY_SEPARATOR;
            $environmentConfigDir = $basePath . $environment . DIRECTORY_SEPARATOR;
            if (!file_exists($environmentConfigDir)) {
                $environmentConfigDir = $basePath . 'default' . DIRECTORY_SEPARATOR;
            }
            $handle = opendir($environmentConfigDir);
            $directoryList = [$environmentConfigDir];
            while (false !== ($filename = readdir($handle))) {
                if ($filename !== '.' && $filename !== '..' && is_dir($environmentConfigDir . $filename)) {
                    $directoryList[] = $environmentConfigDir . $filename . DIRECTORY_SEPARATOR;
                }
            }
            $integrationsConfig = [];
            foreach ($directoryList as $directory) {
                foreach (glob($directory . '*.php') as $filename) {
                    $config = require $filename;
                    $filenameWithoutExtension = basename($filename, '.php');
                    $integrationsConfig[$filenameWithoutExtension] = $config;
                }
            }
            Illuminate\Container\Container::getInstance()->make('config')->set('integrations', $integrationsConfig);
        }
    }

}

if (! function_exists('gen_uid')) {

    /**
     * @return string
     */
    function gen_uid()
    {
        return bin2hex(random_bytes(16));
    }

}

if (! function_exists('get_client_ip')) {

    /**
     * @return string
     */
    function get_client_ip()
    {
        $ip = request()->header('x-real-ip', call_user_func(function() {
            if(request()->headers->has('x-forwarded-for')){
                $ips = request()->headers->get('x-forwarded-for');
                if(is_array($ips))
                {
                    $ip = array_slice($ips, -1);
                    if(filter_var(array_pop($ip), FILTER_VALIDATE_IP))
                    {
                        return $ip;
                    }
                } elseif (filter_var($ips, FILTER_VALIDATE_IP)) {
                    return $ips;
                }
            }

            return request()->getClientIp();
        }));

        if(is_array($ip)){
            return reset($ip);
        }

        return $ip;
    }

}

if (! function_exists('transliterate')) {
    /**
     * @param string $text
     * @param string $direction
     * @return string
     */
    function transliterate(string $text, string $direction = 'ru_en') : string
    {
        $L['ru'] = array(
            'Ё', 'Ж', 'Ц', 'Ч', 'Щ', 'Ш', 'Ы',
            'Э', 'Ю', 'Я', 'ё', 'ж', 'ц', 'ч',
            'ш', 'щ', 'ы', 'э', 'ю', 'я', 'А',
            'Б', 'В', 'Г', 'Д', 'Е', 'З', 'И',
            'Й', 'К', 'Л', 'М', 'Н', 'О', 'П',
            'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ъ',
            'Ь', 'а', 'б', 'в', 'г', 'д', 'е',
            'з', 'и', 'й', 'к', 'л', 'м', 'н',
            'о', 'п', 'р', 'с', 'т', 'у', 'ф',
            'х', 'ъ', 'ь'
        );
        $L['en'] = array(
            "YO", "ZH", "CZ", "CH", "SHH", "SH", "Y'",
            "E'", "YU", "YA", "yo", "zh", "cz", "ch",
            "sh", "shh", "y'", "e'", "yu", "ya", "A",
            "B", "V", "G", "D", "E", "Z", "I",
            "J", "K", "L", "M", "N", "O", "P",
            "R", "S", "T", "U", "F", "X", "''",
            "'", "a", "b", "v", "g", "d", "e",
            "z", "i", "j", "k", "l", "m", "n",
            "o", "p", "r", "s", "t", "u", "f",
            "x", "''", "'"
        );
        // Конвертируем хилый и немощный в великий могучий...
        if ($direction == 'en_ru') {
            $translated = str_replace($L['en'], $L['ru'], $text);
            // Теперь осталось проверить регистр мягкого и твердого знаков.
            $translated = preg_replace('/(?<=[а-яё])Ь/u', 'ь', $translated);
            $translated = preg_replace('/(?<=[а-яё])Ъ/u', 'ъ', $translated);
        } else // И наоборот
            $translated = str_replace($L['ru'], $L['en'], $text);
        // Возвращаем получателю.
        return (string) $translated;
    }
}