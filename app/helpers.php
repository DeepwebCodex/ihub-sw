<?php

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

if (!function_exists('ini')) {
    function ini(string $key = null, $default = null)
    {
        return array_get($_SERVER['ini'], $key, $default);
    }
}