<?php
/**
 * Created by PhpStorm.
 * User: doomsentinel
 * Date: 9/13/16
 * Time: 11:10 AM
 */

namespace App\Components\Integrations\MicroGaming;


use App\Components\Integrations\CodeMappingBase;

class CodeMapping extends CodeMappingBase
{
    const INVALID_AUTH = 'invalid_auth';
    const ACCOUNT_BANNED = 'account_banned';
    const USER_RESTRICTED = 'user_restricted';
    const USER_LIMIT = 'user_limit';

    public static function getMapping(){
        return [
            6000 => [
                'message'   => 'Неопределенная ошибка.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::SERVER_ERROR],
                'default'   => true
            ],
            6001 => [
                'message'   => 'Токен игрока недействителен.',
                'map'       => [],
                'attribute' => 'token',
                'meanings'  => [self::INVALID_TOKEN]
            ],
            6002 => [
                'message'   => 'Срок действия токена игрока истек.',
                'map'       => [],
                'attribute' => 'timestamp',
                'meanings'  => [self::TIME_EXPIRED]
            ],
            6003 => [
                'message'   => 'Учетные данные для аутентификации через API неверны.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::INVALID_AUTH]
            ],
            6101 => [
                'message'   => 'Ошибка при проверке логина. Неверное имя пользователя или пароль.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::INVALID_USER_ID]
            ],
            6102 => [
                'message'   => 'Аккаунт заблокирован.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::ACCOUNT_BANNED]
            ],
            6103 => [
                'message'   => 'Такого аккаунта не существует.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_NOT_FOUND]
            ],
            6104 => [
                'message'   => 'Для игрока действует самоограничение.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_RESTRICTED]
            ],
            6105 => [
                'message'   => 'Игрок еще не принял правила и условия.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_RESTRICTED]
            ],
            6106 => [
                'message'   => 'Обязательное отображение защиты игрока.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_RESTRICTED]
            ],
            6107 => [
                'message'   => 'Ограничение по IP-адресу',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_RESTRICTED]
            ],
            6108 => [
                'message'   => 'Срок действия пароля истек',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_RESTRICTED]
            ],
            6109 => [
                'message'   => 'Срок самоограничения истек, и игрок должен связаться с оператором, чтобы отменить ограничение. После этого активируется период игровой паузы.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_RESTRICTED]
            ],
            6110 => [
                'message'   => 'Срок самоограничения истек, но у игрока период игровой паузы.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_RESTRICTED]
            ],
            6111 => [
                'message'   => 'Аккаунт находится в черном списке.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_RESTRICTED]
            ],
            6112 => [
                'message'   => 'Аккаунт игрока отключен. Относится только к регулируемым рынкам.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_RESTRICTED]
            ],
            6501 => [
                'message'   => 'Уже обработан на основе других сведений.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::DUPLICATE]
            ],
            6503 => [
                'message'   => 'У игрока недостаточно средств.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::NO_MONEY]
            ],
            6505 => [
                'message'   => 'Игрок превысил свой дневной безопасный лимит.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_LIMIT]
            ],
            6506 => [
                'message'   => 'Игрок превысил свой недельный безопасный лимит.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_LIMIT]
            ],
            6507 => [
                'message'   => 'Игрок превысил свой месячный безопасный лимит.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_LIMIT]
            ],
            6508 => [
                'message'   => 'Игрок превысил свою продолжительность игры.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_LIMIT]
            ],
            6509 => [
                'message'   => 'Игрок превысил свой лимит проигрыша.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_LIMIT]
            ],
            6510 => [
                'message'   => 'Игрок не допущен к данной игре.',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_LIMIT]
            ],
            6511 => [
                'message'   => 'Внешняя система с таким именем не существует',
                'map'       => [],
                'attribute' => null,
                'meanings'  => [self::USER_LIMIT]
            ],
        ];
    }
}